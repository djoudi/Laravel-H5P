<?php

/*
 *
 * @Project        Expression project.displayName is undefined on line 5, column 35 in Templates/Licenses/license-default.txt.
 * @Copyright      Djoudi
 * @Created        2017-02-01
 * @Filename       H5pStorage.php
 * @Description
 *
 */

namespace Djoudi\LaravelH5p\Storages;

use App;
use DB;
use Djoudi\LaravelH5p\Eloquents\H5pLibrary;
use Djoudi\LaravelH5p\Eloquents\H5pTmpfile;
use H5peditorStorage;

/**
 * Description of H5pStorage.
 *
 * @author leechanrin
 */
class EditorStorage implements H5peditorStorage
{
    public function alterLibraryFiles(&$files, $libraries)
    {
        $h5p = App::make('LaravelH5p');
        $h5p->alter_assets($files, $libraries, 'editor');
    }

    public function getAvailableLanguages($machineName, $majorVersion, $minorVersion)
    {
    }

    public function getLanguage($machineName, $majorVersion, $minorVersion, $language)
    {
//        $language = 'ja';
        // Load translation field from DB
        $return = DB::select('SELECT hlt.translation FROM h5p_libraries_languages hlt
           JOIN h5p_libraries hl ON hl.id = hlt.library_id
          WHERE hl.name = ?
            AND hl.major_version = ?
            AND hl.minor_version = ?
            AND hlt.language_code = ?', [$machineName, $majorVersion, $minorVersion, $language]
        );

        return $return ? $return[0]->translation : null;
    }

    public function getLibraries($libraries = null)
    {
        $return = [];

        if ($libraries !== null) {
            // Get details for the specified libraries only.
            foreach ($libraries as $library) {
                // Look for library
                $details = H5pLibrary::where('name', $library->name)
                    ->where('major_version', $library->majorVersion)
                    ->where('minor_version', $library->minorVersion)
                    ->whereNotNull('semantics')
                    ->first();

                if ($details) {
                    // Library found, add details to list
                    $library->tutorialUrl = $details->tutorial_url;
                    $library->title = $details->title;
                    $library->runnable = $details->runnable;
                    $library->restricted = $details->restricted === '1' ? true : false;
                    $return[] = $library;
                }
            }
        } else {

            // Load all libraries
            $libraries = [];
            $libraries_result = H5pLibrary::where('runnable', 1)
                ->select([
                    //                        'id',
                    'name',
                    'title',
                    'major_version AS majorVersion',
                    'minor_version AS minorVersion',
                    'patch_version AS patchVersion',
                    //                        'runnable',
                    'restricted',
                    //                        'fullscreen',
                    //                        'embed_types',
                    //                        'preloaded_js',
                    //                        'preloaded_css',
                    //                        'drop_library_css',
                    //                        'semantics',
                    'tutorial_url',
                    //                        'has_icon',
                    //                        'created_at',
                    //                        'updated_at'
                ])
                ->whereNotNull('semantics')
                ->orderBy('name', 'ASC')
                ->get();

            // 모든 버전의 라리브러리가 로드되므로 하나의 가장 최신 라이브러리를 찾는 부분
            foreach ($libraries_result as $library) {
                // Make sure we only display the newest version of a library.
                foreach ($libraries as $key => $existingLibrary) {
                    if ($library->name === $existingLibrary->name) {
                        // Found library with same name, check versions
                        if (($library->majorVersion === $existingLibrary->majorVersion &&
                            $library->minorVersion > $existingLibrary->minorVersion) ||
                            ($library->majorVersion > $existingLibrary->majorVersion)) {
                            // This is a newer version
                            $existingLibrary->isOld = true;
                        } else {
                            // This is an older version
                            $library->isOld = true;
                        }
                    }
                }
                // Check to see if content type should be restricted
                $library->restricted = $library->restricted === '1' ? true : false;

                // Add new library
                $return[] = $library;
            }
        }

        return $return;
    }

    public function keepFile($fileId)
    {
        DB::table('h5p_tmpfiles')->where('path', $fileId)->delete();
    }

    public static function markFileForCleanup($file, $content_id)
    {
        $h5p = App::make('LaravelH5p');
        $path = $h5p->get_h5p_storage();
        if (empty($content_id)) {
            // Should be in editor tmp folder
            $path .= '/editor';
        } else {
            // Should be in content folder
            $path .= '/content/'.$content_id;
        }
        // Add file type to path
        $path .= '/'.$file->getType().'s';
        // Add filename to path
        $path .= '/'.$file->getName();

        H5pTmpfile::create(['path' => $path, 'created_at' => time()]);
        // Keep track of temporary files so they can be cleaned up later.
        //        $wpdb->insert($wpdb->prefix . 'h5p_tmpfiles', array('path' => $path, 'created_at' => time()), array('%s', '%d'));
        // Clear cached value for dirsize.
        //        delete_transient('dirsize_cache');
    }

    public static function removeTemporarilySavedFiles($filePath)
    {
        if (is_dir($filePath)) {
            H5PCore::deleteFileTree($filePath);
        } else {
            unlink($filePath);
        }
    }

    public static function saveFileTemporarily($data, $move_file)
    {
        $h5p = App::make('LaravelH5p');
        $path = $h5p::$interface->getUploadedH5pPath();

        if ($move_file) {
            // Move so core can validate the file extension.
            rename($data, $path);
        } else {
            // Create file from data
            file_put_contents($path, $data);
        }

        return (object) ['dir' => dirname($path), 'fileName' => basename($path)];
    }
}
