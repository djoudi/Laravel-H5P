<?php

namespace Djoudi\LaravelH5p\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Djoudi\LaravelH5p\Eloquents\{H5pContent,H5pLibrary};
//use Djoudi\LaravelH5p\Eloquents\H5pLibrary;
use Djoudi\LaravelH5p\LaravelH5p;
use H5PCore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Log;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $h5p = App::make('LaravelH5p');
        $core = $h5p::$core;
        $interface = $h5p::$interface;
        $not_cached = $interface->getNumNotFiltered();

        $entrys = H5pLibrary::paginate(10);
        $settings = $h5p::get_core([
            'libraryList' => [
                'notCached' => $not_cached,
            ],
            'containerSelector' => '#h5p-admin-container',
            'extraTableClasses' => '',
            'l10n'              => [
                'NA'             => trans('laravel-h5p.common.na'),
                'viewLibrary'    => trans('laravel-h5p.library.viewLibrary'),
                'deleteLibrary'  => trans('laravel-h5p.library.deleteLibrary'),
                'upgradeLibrary' => trans('laravel-h5p.library.upgradeLibrary'),
            ],
        ]);

        foreach ($entrys as $library) {
            $usage = $interface->getLibraryUsage($library->id, $not_cached ? true : false);
            $settings['libraryList']['listData'][] = (object) [
                'id'                     => $library->id,
                'title'                  => $library->title.' ('.H5PCore::libraryVersion($library).')',
                'restricted'             => ($library->restricted ? true : false),
                'numContent'             => $interface->getNumContent($library->id),
                'numContentDependencies' => intval($usage['content']),
                'numLibraryDependencies' => intval($usage['libraries']),
            ];
        }

        $last_update = config('laravel-h5p.h5p_content_type_cache_updated_at');
        $hubOn = config('laravel-h5p.h5p_hub_is_enabled');
        $required_files = $this->assets(['js/h5p-library-list.js']);

        if ($not_cached) {
            $settings['libraryList']['notCached'] = $this->get_not_cached_settings($not_cached);
        } else {
            $settings['libraryList']['notCached'] = 0;
        }

        return view('h5p.library.index', compact('entrys', 'settings', 'last_update', 'hubOn', 'required_files'));
    }

    public function show(Request $request, $id)
    {
        $library = $this->get_library($id);

        // Add settings and translations
        $h5p = App::make('LaravelH5p');
        $core = $h5p::$core;
        $interface = $h5p::$interface;

        $settings = [
            'containerSelector' => '#h5p-admin-container',
        ];

        // Build the translations needed
        $settings['libraryInfo']['translations'] = [
            'noContent'             => trans('laravel-h5p.library.noContent'),
            'contentHeader'         => trans('laravel-h5p.library.contentHeader'),
            'pageSizeSelectorLabel' => trans('laravel-h5p.library.pageSizeSelectorLabel'),
            'filterPlaceholder'     => trans('laravel-h5p.library.filterPlaceholder'),
            'pageXOfY'              => trans('laravel-h5p.library.pageXOfY'),
        ];
        $notCached = $interface->getNumNotFiltered();
        if ($notCached) {
            $settings['libraryInfo']['notCached'] = $this->get_not_cached_settings($notCached);
        } else {
            // List content which uses this library
            $contents = DB::select('SELECT DISTINCT hc.id, hc.title FROM h5p_contents_libraries hcl JOIN h5p_contents hc ON hcl.content_id = hc.id WHERE hcl.library_id = ? ORDER BY hc.title', [$library->id]);

            foreach ($contents as $content) {
                $settings['libraryInfo']['content'][] = [
                    'title' => $content->title,
                    'url'   => route('h5p.show', ['id' => $content->id]),
                ];
            }
        }
        // Build library info
        $settings['libraryInfo']['info'] = [
            'version'         => H5PCore::libraryVersion($library),
            'fullscreen'      => $library->fullscreen ? trans('laravel-h5p.common.yes') : trans('laravel-h5p.common.no'),
            'content_library' => $library->runnable ? trans('laravel-h5p.common.yes') : trans('laravel-h5p.common.no'),
            'used'            => (isset($contents) ? count($contents) : trans('laravel-h5p.common.na')),
        ];

        $required_files = $this->assets(['js/h5p-library-details.js']);

        return view('h5p.library.show', compact('settings', 'required_files', 'library'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'h5p_file' => 'required||max:50000',
        ]);

        if ($request->hasFile('h5p_file') && $request->file('h5p_file')->isValid()) {
            Log::info('Yes Good ');
            $h5p = App::make('LaravelH5p');
            $validator = $h5p::$validator;
            $interface = $h5p::$interface;

            // Content update is skipped because it is new registration
            $content = null;
            $skipContent = true;
            $h5p_upgrade_only = ($request->get('h5p_upgrade_only')) ? true : false;

            rename($request->file('h5p_file')->getPathName(), $interface->getUploadedH5pPath());

            if ($validator->isValidPackage($skipContent, $h5p_upgrade_only)) {
                $storage = $h5p::$storage;
                $storage->savePackage($content, null, $skipContent);
                Log::info('All is OK ');
            }

//            if ($request->get('sync_hub')) {
            //                $h5p::$core->updateContentTypeCache();
            //            }
            // The uploaded file was not a valid H5P package
            @unlink($interface->getUploadedH5pPath());

            return redirect()
                ->route('h5p.library.index')
                ->with('success', trans('laravel-h5p.library.updated'));
        }

        Log::info('Not Good Good ');
        return redirect()
            ->route('h5p.library.index')
            ->with('error', trans('laravel-h5p.library.can_not_updated'));
    }

    public function destroy(Request $request)
    {
        $library = H5pLibrary::findOrFail($request->get('id'));

        $h5p = App::make('LaravelH5p');
        $interface = $h5p::$interface;

        // Error if in use
        $usage = $interface->getLibraryUsage($library);
        if ($usage['content'] !== 0 || $usage['libraries'] !== 0) {
            return redirect()->route('h5p.library.index')
                ->with('error', trans('laravel-h5p.library.used_library_can_not_destoroied'));
        }

        $interface->deleteLibrary($library);

        return redirect()
            ->route('h5p.library.index')
            ->with('success', trans('laravel-h5p.library.destroyed'));
    }

    public function clear(Request $request)
    {
        $h5p = App::make('LaravelH5p');
        $core = $h5p::$core;

        // Do as many as we can in five seconds.
        $start = microtime(true);
        $contents = H5pContent::where('filtered', '')->get();

        $done = 0;

        foreach ($contents as $content) {
            $content = $core->loadContent($content->id);
            $core->filterParameters($content);
            $done++;
            if ((microtime(true) - $start) > 5) {
                break;
            }
        }

        $count = intval(count($contents) - $done);

        return redirect()->route('h5p.library.index')
            ->with('success', trans('laravel-h5p.library.cleared'));
    }

    public function restrict(Request $request)
    {
        $entry = H5pLibrary::where('id', $request->get('id'))->first();

        if ($entry) {
            if ($entry->restricted == '1') {
                $entry->restricted = '0';
            } else {
                $entry->restricted = '1';
            }
            $entry->update();
        }

        return response()->json($entry);
    }

    private function assets($scripts = [], $styles = [])
    {
        $prefix = 'assets/vendor/h5p/h5p-core/';
        $return = [
            'scripts' => [],
            'styles'  => [],
        ];

        foreach (H5PCore::$adminScripts as $script) {
            $return['scripts'][] = $prefix.$script;
        }

        $return['styles'][] = $prefix.'styles/h5p.css';
        $return['styles'][] = $prefix.'styles/h5p-admin.css';

        if ($scripts) {
            foreach ($scripts as $script) {
                $return['scripts'][] = $prefix.$script;
            }
        }
        if ($styles) {
            foreach ($styles as $style) {
                $return['styles'][] = $prefix.$style;
            }
        }

        return $return;
    }

    //@TODO The following is a feature from the existing WordPress plug-in, but not all features need to be developed.
    // Then connect to the new method as needed and implement it
    //https://github.com/h5p/h5p-wordpress-plugin/blob/90a7bb4fa3d927eda401470bc599c9f1d7508ffe/admin/class-h5p-library-admin.php
    //----------------------------------------------------------------------------------

    /**
     * Load library.
     *
     * @since 1.1.0
     *
     * @param int $id optional
     */
    private function get_library($id = NULL)
    {
//        if ($this->library !== NULL) {
        //            return $this->library; // Return the current loaded library.
        //        }
        if ($id === NULL) {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        }

        // Try to find content with $id.
        return H5pLibrary::findOrFail($id);
    }

    /**
     * Display admin interface for managing content libraries.
     *
     * @since 1.1.0
     */
    public function display_libraries_page()
    {
        switch (filter_input(INPUT_GET, 'task', FILTER_SANITIZE_STRING)) {
        case NULL:
            $this->display_libraries();

            return;
        case 'show':
            $this->display_library_details();

            return;
        case 'delete':
            $library = $this->get_library();
            H5P_Plugin_Admin::print_messages();
            if ($library) {
                include_once 'views/library-delete.php';
            }

            return;
        case 'upgrade':
            $library = $this->get_library();
            if ($library) {
                $settings = $this->display_content_upgrades($library);
            }
            include_once 'views/library-content-upgrade.php';
            if (isset($settings)) {
                $h5p = H5P_Plugin::get_instance();
                $h5p->print_settings($settings, 'H5PAdminIntegration');
            }

            return;
        }
        echo '<div class="wrap"><h2>'.esc_html__('Unknown task.').'</h2></div>';
    }

    /**
     * JavaScript settings needed to rebuild content caches.
     *
     * @since 1.1.0
     */
    private function get_not_cached_settings($num)
    {
        return [
            'num'      => $num,
            'url'      => route('h5p.ajax.rebuild-cache'),
            'message'  => __('Not all content has gotten their cache rebuilt. This is required to be able to delete libraries, and to display how many contents that uses the library.'),
            'progress' => __('1 content need to get its cache rebuilt. :num contents needs to get their cache rebuilt.', ['num' => $num]),
//            'button' => __('Rebuild cache')
        ];
    }

    /*
     * Display a list of all h5p content libraries.
     *
     * @since 1.1.0
     */
//    private function display_libraries() {
    //        $h5p = H5P_Plugin::get_instance();
    //        $core = $h5p->get_h5p_instance('core');
    //        $interface = $h5p->get_h5p_instance('interface');
    //        $not_cached = $interface->getNumNotFiltered();
    //        $libraries = $interface->loadLibraries();
    //        $settings = array(
    //            'containerSelector' => '#h5p-admin-container',
    //            'extraTableClasses' => 'wp-list-table widefat fixed',
    //            'l10n' => array(
    //                'NA' => __('N/A'),
    //                'viewLibrary' => __('View library details'),
    //                'deleteLibrary' => __('Delete library'),
    //                'upgradeLibrary' => __('Upgrade library content')
    //            )
    //        );
    //        // Add settings for each library
    //        $i = 0;
    //        foreach ($libraries as $versions) {
    //            foreach ($versions as $library) {
    //                $usage = $interface->getLibraryUsage($library->id, $not_cached ? TRUE : FALSE);
    //                if ($library->runnable) {
    //                    $upgrades = $core->getUpgrades($library, $versions);
    //                    $upgradeUrl = empty($upgrades) ? FALSE : admin_url('admin.php?page=h5p_libraries&task=upgrade&id=' . $library->id . '&destination=' . admin_url('admin.php?page=h5p_libraries'));
    //                    $restricted = ($library->restricted ? TRUE : FALSE);
    //                    $restricted_url = admin_url('admin-ajax.php?action=h5p_restrict_library' .
    //                            '&id=' . $library->id .
    //                            '&token=' . wp_create_nonce('h5p_library_' . $i) .
    //                            '&token_id=' . $i .
    //                            '&restrict=' . ($library->restricted === '1' ? 0 : 1));
    //                } else {
    //                    $upgradeUrl = NULL;
    //                    $restricted = NULL;
    //                    $restricted_url = NULL;
    //                }
    //                $contents_count = $interface->getNumContent($library->id);
    //                $settings['libraryList']['listData'][] = array(
    //                    'title' => $library->title . ' (' . H5PCore::libraryVersion($library) . ')',
    //                    'restricted' => $restricted,
    //                    'restrictedUrl' => $restricted_url,
    //                    'numContent' => $contents_count === 0 ? '' : $contents_count,
    //                    'numContentDependencies' => $usage['content'] < 1 ? '' : $usage['content'],
    //                    'numLibraryDependencies' => $usage['libraries'] === 0 ? '' : $usage['libraries'],
    //                    'upgradeUrl' => $upgradeUrl,
    //                    'detailsUrl' => admin_url('admin.php?page=h5p_libraries&task=show&id=' . $library->id),
    //                    'deleteUrl' => admin_url('admin.php?page=h5p_libraries&task=delete&id=' . $library->id)
    //                );
    //                $i++;
    //            }
    //        }
    //        // Translations
    //        $settings['libraryList']['listHeaders'] = array(
    //            __('Title'),
    //            __('Restricted'),
    //            array(
    //                'text' => __('Contents'),
    //                'class' => 'h5p-admin-center'
    //            ),
    //            array(
    //                'text' => __('Contents using it'),
    //                'class' => 'h5p-admin-center'
    //            ),
    //            array(
    //                'text' => __('Libraries using it'),
    //                'class' => 'h5p-admin-center'
    //            ),
    //            __('Actions')
    //        );
    //
    //        // Make it possible to rebuild all caches.
    //        if ($not_cached) {
    //            $settings['libraryList']['notCached'] = $this->get_not_cached_settings($not_cached);
    //        }
    //        // Assets
    //        $this->add_admin_assets();
    //        H5P_Plugin_Admin::add_script('library-list', 'js/h5p-library-list.js');
    //        // Load content type cache time
    //        $last_update = get_option('h5p_content_type_cache_updated_at', '');
    //        $hubOn = config('laravel-h5p.h5p_hub_is_enabled');
    //        include_once('views/libraries.php');
    //        $h5p->print_settings($settings, 'H5PAdminIntegration');
    //    }

    /*
     * Display details for a given content library.
     *
     * @since 1.1.0
     */
//    private function display_library_details() {
    //        $library = $this->get_library();
    //        H5P_Plugin_Admin::print_messages();
    //        if (!$library) {
    //            return;
    //        }
    //        // Add settings and translations
    //        $h5p = H5P_Plugin::get_instance();
    //        $interface = $h5p->get_h5p_instance('interface');
    //        $settings = array(
    //            'containerSelector' => '#h5p-admin-container',
    //        );
    //        // Build the translations needed
    //        $settings['libraryInfo']['translations'] = array(
    //            'noContent' => __('No content is using this library'),
    //            'contentHeader' => __('Content using this library'),
    //            'pageSizeSelectorLabel' => __('Elements per page'),
    //            'filterPlaceholder' => __('Filter content'),
    //            'pageXOfY' => __('Page $x of $y'),
    //        );
    //        $notCached = $interface->getNumNotFiltered();
    //        if ($notCached) {
    //            $settings['libraryInfo']['notCached'] = $this->get_not_cached_settings($notCached);
    //        } else {
    //            // List content which uses this library
    //            $contents = $wpdb->get_results($wpdb->prepare(
    //                            "SELECT DISTINCT hc.id, hc.title
    //            FROM h5p_contents_libraries hcl
    //            JOIN h5p_contents hc ON hcl.content_id = hc.id
    //            WHERE hcl.library_id = %d
    //            ORDER BY hc.title", $library->id
    //                    )
    //            );
    //            foreach ($contents as $content) {
    //                $settings['libraryInfo']['content'][] = array(
    //                    'title' => $content->title,
    //                    'url' => admin_url('admin.php?page=h5p&task=show&id=' . $content->id),
    //                );
    //            }
    //        }
    //        // Build library info
    //        $settings['libraryInfo']['info'] = array(
    //            __('Version') => H5PCore::libraryVersion($library),
    //            __('Fullscreen') => $library->fullscreen ? __('Yes') : __('No'),
    //            __('Content library') => $library->runnable ? __('Yes') : __('No'),
    //            __('Used by') => (isset($contents) ? sprintf(_n('1 content', '%d contents', count($contents)), count($contents)) : __('N/A')),
    //        );
    //        $this->add_admin_assets();
    //        H5P_Plugin_Admin::add_script('library-list', 'js/h5p-library-details.js');
    //        include_once('views/library-details.php');
    //        $h5p->print_settings($settings, 'H5PAdminIntegration');
    //    }

    /*
     * Display a list of all h5p content libraries.
     *
     * @since 1.1.0
     */
//    private function display_content_upgrades($library) {
    //        global $wpdb;
    //        $h5p = H5P_Plugin::get_instance();
    //        $core = $h5p->get_h5p_instance('core');
    //        $interface = $h5p->get_h5p_instance('interface');
    //        $versions = $wpdb->get_results($wpdb->prepare(
    //                        "SELECT hl2.id, hl2.name, hl2.title, hl2.major_version, hl2.minor_version, hl2.patch_version
    //          FROM h5p_libraries hl1
    //          JOIN h5p_libraries hl2
    //            ON hl2.name = hl1.name
    //          WHERE hl1.id = %d
    //          ORDER BY hl2.title ASC, hl2.major_version ASC, hl2.minor_version ASC", $library->id
    //        ));
    //        foreach ($versions as $version) {
    //            if ($version->id === $library->id) {
    //                $upgrades = $core->getUpgrades($version, $versions);
    //                break;
    //            }
    //        }
    //        if (count($versions) < 2) {
    //            H5P_Plugin_Admin::set_error(__('There are no available upgrades for this library.'));
    //            return NULL;
    //        }
    //        // Get num of contents that can be upgraded
    //        $contents = $interface->getNumContent($library->id);
    //        if (!$contents) {
    //            H5P_Plugin_Admin::set_error(__("There's no content instances to upgrade."));
    //            return NULL;
    //        }
    //        $contents_plural = sprintf(_n('1 content', '%d contents', $contents), $contents);
    //        // Add JavaScript settings
    //        $return = filter_input(INPUT_GET, 'destination');
    //        $settings = array(
    //            'containerSelector' => '#h5p-admin-container',
    //            'libraryInfo' => array(
    //                'message' => sprintf(__('You are about to upgrade %s. Please select upgrade version.'), $contents_plural),
    //                'inProgress' => __('Upgrading to %ver...'),
    //                'error' => __('An error occurred while processing parameters:'),
    //                'errorData' => __('Could not load data for library %lib.'),
    //                'errorContent' => __('Could not upgrade content %id:'),
    //                'errorScript' => __('Could not load upgrades script for %lib.'),
    //                'errorParamsBroken' => __('Parameters are broken.'),
    //                'done' => sprintf(__('You have successfully upgraded %s.'), $contents_plural) . ($return ? '<br/><a href="' . $return . '">' . __('Return') . '</a>' : ''),
    //                'library' => array(
    //                    'name' => $library->name,
    //                    'version' => $library->major_version . '.' . $library->minor_version,
    //                ),
    //                'libraryBaseUrl' => admin_url('admin-ajax.php?action=h5p_content_upgrade_library&library='),
    //                'scriptBaseUrl' => plugins_url('h5p/js'),
    //                'buster' => '?ver=' . H5P_Plugin::VERSION,
    //                'versions' => $upgrades,
    //                'contents' => $contents,
    //                'buttonLabel' => __('Upgrade'),
    //                'infoUrl' => admin_url('admin-ajax.php?action=h5p_content_upgrade_progress&id=' . $library->id),
    //                'total' => $contents,
    //                'token' => wp_create_nonce('h5p_content_upgrade')
    //            )
    //        );
    //        $this->add_admin_assets();
    //        H5P_Plugin_Admin::add_script('version', 'js/h5p-version.js');
    //        H5P_Plugin_Admin::add_script('content-upgrade', 'js/h5p-content-upgrade.js');
    //        return $settings;
    //    }

    /*
     * Helps rebuild all content caches.
     *
     * @since 1.1.0
     */
//    public function ajax_rebuild_cache() {
    //        global $wpdb;
    //        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //            exit; // POST is required
    //        }
    //        $h5p = H5P_Plugin::get_instance();
    //        $core = $h5p->get_h5p_instance('core');
    //        // Do as many as we can in five seconds.
    //        $start = microtime(TRUE);
    //        $contents = $wpdb->get_results(
    //                "SELECT id
    //          FROM h5p_contents
    //          WHERE filtered = ''"
    //        );
    //        $done = 0;
    //        foreach ($contents as $content) {
    //            $content = $core->loadContent($content->id);
    //            $core->filterParameters($content);
    //            $done++;
    //            if ((microtime(TRUE) - $start) > 5) {
    //                break;
    //            }
    //        }
    //        print (count($contents) - $done);
    //        exit;
    //    }

    /*
     * Add generic admin interface assets.
     *
     * @since 1.1.0
     */
//    private function add_admin_assets() {
    //        foreach (H5PCore::$adminScripts as $script) {
    //            H5P_Plugin_Admin::add_script('admin-' . $script, '' . $script);
    //        }
    //        H5P_Plugin_Admin::add_style('h5p', 'styles/h5p.css');
    //        H5P_Plugin_Admin::add_style('admin', 'styles/h5p-admin.css');
    //    }
    //
    //    /**
    //     * JavaScript settings needed to rebuild content caches.
    //     *
    //     * @since 1.1.0
    //     */
    //    private function get_not_cached_settings($num) {
    //        return array(
    //            'num' => $num,
    //            'url' => admin_url('admin-ajax.php?action=h5p_rebuild_cache'),
    //            'message' => __('Not all content has gotten their cache rebuilt. This is required to be able to delete libraries, and to display how many contents that uses the library.'),
    //            'progress' => sprintf(_n('1 content need to get its cache rebuilt.', '%d contents needs to get their cache rebuilt.', $num), $num),
    //            'button' => __('Rebuild cache')
    //        );
    //    }

    /*
     * AJAX processing for content upgrade script.
     */
//    public function ajax_upgrade_progress() {
    //        global $wpdb;
    //        header('Cache-Control: no-cache');
    //        if (!wp_verify_nonce(filter_input(INPUT_POST, 'token'), 'h5p_content_upgrade')) {
    //            print __('Error, invalid security token!');
    //            exit;
    //        }
    //        $library_id = filter_input(INPUT_GET, 'id');
    //        if (!$library_id) {
    //            print __('Error, missing library!');
    //            exit;
    //        }
    //        // Get the library we're upgrading to
    //        $to_library = $wpdb->get_row($wpdb->prepare(
    //                        "SELECT id, name, major_version, minor_version
    //          FROM h5p_libraries
    //          WHERE id = %d", filter_input(INPUT_POST, 'libraryId')
    //        ));
    //        if (!$to_library) {
    //            print __('Error, invalid library!');
    //            exit;
    //        }
    //        // Prepare response
    //        $out = new stdClass();
    //        $out->params = array();
    //        $out->token = wp_create_nonce('h5p_content_upgrade');
    //        // Get updated params
    //        $params = filter_input(INPUT_POST, 'params');
    //        if ($params !== NULL) {
    //            // Update params.
    //            $params = json_decode($params);
    //            foreach ($params as $id => $param) {
    //                $wpdb->update(
    //                        $wpdb->prefix . 'h5p_contents', array(
    //                    'updated_at' => current_time('mysql', 1),
    //                    'parameters' => $param,
    //                    'library_id' => $to_library->id,
    //                    'filtered' => ''
    //                        ), array(
    //                    'id' => $id
    //                        ), array(
    //                    '%s',
    //                    '%s',
    //                    '%d',
    //                    '%s'
    //                        ), array(
    //                    '%d'
    //                        )
    //                );
    //                // Log content upgrade successful
    //                new H5pEvent('content', 'upgrade', $id, $wpdb->get_var($wpdb->prepare("SELECT title FROM h5p_contents WHERE id = %d", $id)), $to_library->name, $to_library->major_version . '.' . $to_library->minor_version);
    //            }
    //        }
    //        // Prepare our interface
    //        $h5p = H5P_Plugin::get_instance();
    //        $interface = $h5p->get_h5p_instance('interface');
    //        // Get number of contents for this library
    //        $out->left = $interface->getNumContent($library_id);
    //        if ($out->left) {
    //            // Find the 10 first contents using library and add to params
    //            $contents = $wpdb->get_results($wpdb->prepare(
    //                            "SELECT id, parameters
    //            FROM h5p_contents
    //            WHERE library_id = %d
    //            LIMIT 40", $library_id
    //            ));
    //            foreach ($contents as $content) {
    //                $out->params[$content->id] = $content->parameters;
    //            }
    //        }
    //        header('Content-type: application/json');
    //        print json_encode($out);
    //        exit;
    //    }
    //
    //    /**
    //     * AJAX loading of libraries for content upgrade script.
    //     *
    //     * @since 1.1.0
    //     * @param string $name
    //     * @param int $major
    //     * @param int $minor
    //     */
    //    public function ajax_upgrade_library() {
    //        header('Cache-Control: no-cache');
    //        $library_string = filter_input(INPUT_GET, 'library');
    //        if (!$library_string) {
    //            print __('Error, missing library!');
    //            exit;
    //        }
    //        $library_parts = explode('/', $library_string);
    //        if (count($library_parts) !== 4) {
    //            print __('Error, invalid library!');
    //            exit;
    //        }
    //        $library = (object) array(
    //                    'name' => $library_parts[1],
    //                    'version' => (object) array(
    //                        'major' => $library_parts[2],
    //                        'minor' => $library_parts[3]
    //                    )
    //        );
    //        $h5p = H5P_Plugin::get_instance();
    //        $core = $h5p->get_h5p_instance('core');
    //        $library->semantics = $core->loadLibrarySemantics($library->name, $library->version->major, $library->version->minor);
    //        if ($library->semantics === NULL) {
    //            print __('Error, could not library semantics!');
    //            exit;
    //        }
    //        // TODO: Library development mode
    ////    if ($core->development_mode & H5PDevelopment::MODE_LIBRARY) {
    ////      $dev_lib = $core->h5pD->getLibrary($library->name, $library->version->major, $library->version->minor);
    ////    }
    //        if (isset($dev_lib)) {
    //            $upgrades_script_path = $upgrades_script_url = $dev_lib['path'] . '/upgrades.js';
    //        } else {
    //            $suffix = '/libraries/' . $library->name . '-' . $library->version->major . '.' . $library->version->minor . '/upgrades.js';
    //            $upgrades_script_path = $h5p->get_h5p_path() . $suffix;
    //            $upgrades_script_url = $h5p->get_h5p_url() . $suffix;
    //        }
    //        if (file_exists($upgrades_script_path)) {
    //            $library->upgradesScript = $upgrades_script_url;
    //        }
    //        header('Content-type: application/json');
    //        print json_encode($library);
    //        exit;
    //    }
}
