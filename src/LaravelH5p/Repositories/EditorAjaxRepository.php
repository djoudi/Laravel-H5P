<?php

/*
 *
 * @Project
 * @Copyright      Djoudi
 * @Created        2018-02-13
 * @Filename       EditorAjaxRepogitory.php
 * @Description
 *
 */

namespace Djoudi\LaravelH5p\Repositories;

use DB;
use Djoudi\LaravelH5p\Eloquents\H5pLibrariesHubCache;
use H5PEditorAjaxInterface;
use Illuminate\Support\Facades\Auth;

class EditorAjaxRepository implements H5PEditorAjaxInterface
{
    /**
     * Gets recently used libraries for the current author.
     *
     * @return array machine names. The first element in the array is the
     *               most recently used.
     */
    /*  public function getAuthorsRecentlyUsedLibraries() {
        global $wpdb;
        $recently_used = array();
        $result = $wpdb->get_results($wpdb->prepare(
         "SELECT library_name, max(created_at) AS max_created_at
             FROM {$wpdb->prefix}h5p_events
            WHERE type='content' AND sub_type = 'create' AND user_id = %d
         GROUP BY library_name
         ORDER BY max_created_at DESC",
          get_current_user_id()
        ));
        foreach ($result as $row) {
          $recently_used[] = $row->library_name;
        }
        return $recently_used;
      }
    */
    public function getAuthorsRecentlyUsedLibraries()
    {
        // Get latest version of local libraries
        $major_versions_sql = 'SELECT hl.name,
                MAX(hl.major_version) AS majorVersion
           FROM h5p_libraries hl
          WHERE hl.runnable = 1
       GROUP BY hl.name';

        $minor_versions_sql = "SELECT hl2.name,
                 hl2.majorVersion,
                 MAX(hl2.minorVersion) AS minorVersion
            FROM ({$major_versions_sql}) hl1
            JOIN h5p_libraries hl2
              ON hl1.name = hl2.name
             AND hl1.majorVersion = hl2.majorVersion
        GROUP BY hl2.name, hl2.majorVersion";

        return DB::select("SELECT hl4.id,
                hl4.name AS machine_name,
                hl4.major_version,
                hl4.minor_version,
                hl4.patch_version,
                hl4.restricted,
                hl4.has_icon
           FROM ({$minor_versions_sql}) hl3
           JOIN h5p_libraries hl4
             ON hl3.name = hl4.name
            AND hl3.major_version = hl4.major_version
            AND hl3.minor_version = hl4.minor_version
       GROUP BY hl4.name, hl4.major_version, hl4.minor_version");
    }

    public function getContentTypeCache($machineName = null)
    {
        $where = H5pLibrariesHubCache::select();
        if ($machineName) {
            return $where->where('machine_name', $machineName)->pluck('id', 'is_recommended');
        } else {
            return $where->where('machine_name', $machineName)->get();
        }
    }

    public function getLatestLibraryVersions()
    {
        $recently_used = [];
        $result = DB::table('h5p_events')
            ->select([
                'library_name',
                'max(created_at) AS max_created_at',
            ])
            ->where('type', 'content')
            ->where('sub_type', 'create')
            ->where('user_id', Auth::id())
            ->groupBy('library_name')
            ->orderBy('max_created_at', 'DESC')
            ->get();

        foreach ($result as $row) {
            $recently_used[] = $row->library_name;
        }

        dd($recently_used);
        exit;

        return $recently_used;
    }

    public function validateEditorToken($token)
    {
        // return (Helpers::nonce($token) == 'h5p_editor_ajax');
        return true;
    }

    public function getTranslations($libraries, $language_code)
    {
    }
}
