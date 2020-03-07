<?php

/*
 *
 * @Project        Expression project.displayName is undefined on line 5, column 35 in Templates/Licenses/license-default.txt.
 * @Copyright      Djoudi
 * @Created        2017-02-11
 * @Filename       FilesStorage.php
 * @Description
 *
 */

namespace Djoudi\LaravelH5p\Storages;

use H5PFileStorage;

//use Illuminate\Filesystem\Filesystem;
//use Symfony\Component\Finder\Finder;

class LaravelH5pStorage implements H5PFileStorage
{
    private $path;
    private $alteditorpath;

    /**
     * The great Constructor!
     *
     * @param string $path
     *                              The base location of H5P files
     * @param string $alteditorpath
     *                              Optional. Use a different editor path
     */
    public function __construct($path, $alteditorpath = null)
    {
        // Set H5P storage path
        $this->path = $path;
        $this->alteditorpath = $alteditorpath;
    }

    public function hasPresave($libraryName, $developmentPath = null)
    {
    }

    public function getUpgradeScript($machineName, $majorVersion, $minorVersion)
    {
    }

    /**
     * Store the library folder.
     *
     * @param array $library
     *                       Library properties
     */
    public function saveLibrary($library)
    {
        $dest = $this->path.'/libraries/'.\H5PCore::libraryToString($library, true);

        // Make sure destination dir doesn't exist
        \H5PCore::deleteFileTree($dest);

        // Move library folder
        self::copyFileTree($library['uploadDirectory'], $dest);
    }

    /**
     * Store the content folder.
     *
     * @param string $source
     *                        Path on file system to content directory.
     * @param array  $content
     *                        Content properties
     */
    public function saveContent($source, $content)
    {
        $dest = "{$this->path}/content/{$content['id']}";

        // Remove any old content
        \H5PCore::deleteFileTree($dest);

        self::copyFileTree($source, $dest);
    }

    /**
     * Remove content folder.
     *
     * @param array $content
     *                       Content properties
     */
    public function deleteContent($content)
    {
        \H5PCore::deleteFileTree("{$this->path}/content/{$content['id']}");
    }

    /**
     * Creates a stored copy of the content folder.
     *
     * @param string $id
     *                      Identifier of content to clone.
     * @param int    $newId
     *                      The cloned content's identifier
     */
    public function cloneContent($id, $newId)
    {
        $path = $this->path.'/content/';
        if (file_exists($path.$id)) {
            self::copyFileTree($path.$id, $path.$newId);
        }
    }

    /**
     * Get path to a new unique tmp folder.
     *
     * @return string
     *                Path
     */
    public function getTmpPath()
    {
        $temp = "{$this->path}/temp";
        self::dirReady($temp);

        return "{$temp}/".uniqid('h5p-');
    }

    /**
     * Fetch content folder and save in target directory.
     *
     * @param int    $id
     *                       Content identifier
     * @param string $target
     *                       Where the content folder will be saved
     */
    public function exportContent($id, $target)
    {
        $source = "{$this->path}/content/{$id}";
        if (file_exists($source)) {
            // Copy content folder if it exists
            self::copyFileTree($source, $target);
        } else {
            // No contnet folder, create emty dir for content.json
            self::dirReady($target);
        }
    }

    /**
     * Fetch library folder and save in target directory.
     *
     * @param array  $library
     *                                Library properties
     * @param string $target
     *                                Where the library folder will be saved
     * @param string $developmentPath
     *                                Folder that library resides in
     */
    public function exportLibrary($library, $target, $developmentPath = null)
    {
        $folder = \H5PCore::libraryToString($library, true);
        $srcPath = ($developmentPath === null ? "/libraries/{$folder}" : $developmentPath);
        self::copyFileTree("{$this->path}{$srcPath}", "{$target}/{$folder}");
    }

    /**
     * Save export in file system.
     *
     * @param string $source
     *                         Path on file system to temporary export file.
     * @param string $filename
     *                         Name of export file.
     *
     * @throws Exception Unable to save the file
     */
    public function saveExport($source, $filename)
    {
        $this->deleteExport($filename);

        if (!self::dirReady("{$this->path}/exports")) {
            throw new Exception('Unable to create directory for H5P export file.');
        }

        if (!copy($source, "{$this->path}/exports/{$filename}")) {
            throw new Exception('Unable to save H5P export file.');
        }
    }

    /**
     * Removes given export file.
     *
     * @param string $filename
     */
    public function deleteExport($filename)
    {
        $target = "{$this->path}/exports/{$filename}";
        if (file_exists($target)) {
            unlink($target);
        }
    }

    /**
     * Check if the given export file exists.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function hasExport($filename)
    {
        $target = "{$this->path}/exports/{$filename}";

        return file_exists($target);
    }

    /**
     * Will concatenate all JavaScrips and Stylesheets into two files in order
     * to improve page performance.
     *
     * @param array  $files
     *                      A set of all the assets required for content to display
     * @param string $key
     *                      Hashed key for cached asset
     */
    public function cacheAssets(&$files, $key)
    {
        foreach ($files as $type => $assets) {
            if (empty($assets)) {
                continue; // Skip no assets
            }

            $content = '';
            foreach ($assets as $asset) {
                // Get content from asset file
                $assetContent = file_get_contents($this->path.$asset->path);
                $cssRelPath = preg_replace('/[^\/]+$/', '', $asset->path);

                // Get file content and concatenate
                if ($type === 'scripts') {
                    $content .= $assetContent.";\n";
                } else {
                    // Rewrite relative URLs used inside stylesheets
                    $content .= preg_replace_callback(
                        '/url\([\'"]?([^"\')]+)[\'"]?\)/i',
                        function ($matches) use ($cssRelPath) {
                            if (preg_match("/^(data:|([a-z0-9]+:)?\/)/i", $matches[1]) === 1) {
                                return $matches[0]; // Not relative, skip
                            }

                            return 'url("../'.$cssRelPath.$matches[1].'")';
                        },
                        $assetContent
                    )."\n";
                }
            }

            self::dirReady("{$this->path}/cachedassets");
            $ext = ($type === 'scripts' ? 'js' : 'css');
            $outputfile = "/cachedassets/{$key}.{$ext}";
            file_put_contents($this->path.$outputfile, $content);
            $files[$type] = [(object) [
                'path'    => $outputfile,
                'version' => '',
            ]];
        }
    }

    /**
     * Will check if there are cache assets available for content.
     *
     * @param string $key
     *                    Hashed key for cached asset
     *
     * @return array
     */
    public function getCachedAssets($key)
    {
        $files = [];

        $js = "/cachedassets/{$key}.js";
        if (file_exists($this->path.$js)) {
            $files['scripts'] = [(object) [
                'path'    => $js,
                'version' => '',
            ]];
        }

        $css = "/cachedassets/{$key}.css";
        if (file_exists($this->path.$css)) {
            $files['styles'] = [(object) [
                'path'    => $css,
                'version' => '',
            ]];
        }

        return empty($files) ? null : $files;
    }

    /**
     * Remove the aggregated cache files.
     *
     * @param array $keys
     *                    The hash keys of removed files
     */
    public function deleteCachedAssets($keys)
    {
        foreach ($keys as $hash) {
            foreach (['js', 'css'] as $ext) {
                $path = "{$this->path}/cachedassets/{$hash}.{$ext}";
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    /**
     * Read file content of given file and then return it.
     *
     * @param string $file_path
     *
     * @return string
     */
    public function getContent($file_path)
    {
        return file_get_contents($file_path);
    }

    /**
     * Save files uploaded through the editor.
     * The files must be marked as temporary until the content form is saved.
     *
     * @param \H5peditorFile $file
     * @param int            $contentid
     */
    public function saveFile($file, $contentId)
    {
        // Prepare directory
        if (empty($contentId)) {
            // Should be in editor tmp folder
            $path = $this->getEditorPath();
        } else {
            // Should be in content folder
            $path = $this->path.'/content/'.$contentId;
        }
        $path .= '/'.$file->getType().'s';

        self::dirReady($path);

        // Add filename to path
        $path .= '/'.$file->getName();

        $fileData = $file->getData();
        if ($fileData) {
            file_put_contents($path, $fileData);
        } else {
            copy($_FILES['file']['tmp_name'], $path);
        }

        return $file;
    }

    /**
     * Copy a file from another content or editor tmp dir.
     * Used when copy pasting content in H5P Editor.
     *
     * @param string     $file   path + name
     * @param string|int $fromid Content ID or 'editor' string
     * @param int        $toid   Target Content ID
     */
    public function cloneContentFile($file, $fromId, $toId)
    {
        // Determine source path
        if ($fromId === 'editor') {
            $sourcepath = $this->getEditorPath();
        } else {
            $sourcepath = "{$this->path}/content/{$fromId}";
        }
        $sourcepath .= '/'.$file;

        // Determine target path
        $filename = basename($file);
        $filedir = str_replace($filename, '', $file);
        $targetpath = "{$this->path}/content/{$toId}/{$filedir}";

        // Make sure it's ready
        self::dirReady($targetpath);

        $targetpath .= $filename;

        // Check to see if source exist and if target doesn't
        if (!file_exists($sourcepath) || file_exists($targetpath)) {
            return; // Nothing to copy from or target already exists
        }

        copy($sourcepath, $targetpath);
    }

    /**
     * Copy a content from one directory to another. Defaults to cloning
     * content from the current temporary upload folder to the editor path.
     *
     * @param string $source    path to source directory
     * @param string $contentId Id of content
     *
     * @return object Object containing h5p json and content json data
     */
    public function moveContentDirectory($source, $contentId = null)
    {
        if ($source === null) {
            return;
        }

        if ($contentId === null || $contentId == 0) {
            $target = $this->getEditorPath();
        } else {
            // Use content folder
            $target = "{$this->path}/content/{$contentId}";
        }

        $contentSource = $source.DIRECTORY_SEPARATOR.'content';
        $contentFiles = array_diff(scandir($contentSource), ['.', '..', 'content.json']);
        foreach ($contentFiles as $file) {
            if (is_dir("{$contentSource}/{$file}")) {
                self::copyFileTree("{$contentSource}/{$file}", "{$target}/{$file}");
            } else {
                copy("{$contentSource}/{$file}", "{$target}/{$file}");
            }
        }

        // Successfully loaded content json of file into editor
        $h5pJson = $this->getContent($source.DIRECTORY_SEPARATOR.'h5p.json');
        $contentJson = $this->getContent($contentSource.DIRECTORY_SEPARATOR.'content.json');

        return (object) [
            'h5pJson'     => $h5pJson,
            'contentJson' => $contentJson,
        ];
    }

    /**
     * Checks to see if content has the given file.
     * Used when saving content.
     *
     * @param string $file      path + name
     * @param int    $contentId
     *
     * @return string File ID or NULL if not found
     */
    public function getContentFile($file, $contentId)
    {
        $path = "{$this->path}/content/{$contentId}/{$file}";

        return file_exists($path) ? $path : null;
    }

    /**
     * Checks to see if content has the given file.
     * Used when saving content.
     *
     * @param string $file      path + name
     * @param int    $contentid
     *
     * @return string|int File ID or NULL if not found
     */
    public function removeContentFile($file, $contentId)
    {
        $path = "{$this->path}/content/{$contentId}/{$file}";
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Check if server setup has write permission to
     * the required folders.
     *
     * @return bool True if site can write to the H5P files folder
     */
    public function hasWriteAccess()
    {
        return self::dirReady($this->path);
    }

    /**
     * Recursive function for copying directories.
     *
     * @param string $source
     *                            From path
     * @param string $destination
     *                            To path
     *
     * @throws Exception Unable to copy the file
     *
     * @return bool
     *              Indicates if the directory existed.
     */
    private static function copyFileTree($source, $destination)
    {
        if (!self::dirReady($destination)) {
            throw new \Exception('unabletocopy');
        }

        $ignoredFiles = self::getIgnoredFiles("{$source}/.h5pignore");

        $dir = opendir($source);
        if ($dir === false) {
            trigger_error('Unable to open directory '.$source, E_USER_WARNING);

            throw new \Exception('unabletocopy');
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..') && $file != '.git' && $file != '.gitignore' && !in_array($file, $ignoredFiles)) {
                if (is_dir("{$source}/{$file}")) {
                    self::copyFileTree("{$source}/{$file}", "{$destination}/{$file}");
                } else {
                    copy("{$source}/{$file}", "{$destination}/{$file}");
                }
            }
        }
        closedir($dir);
    }

    /**
     * Retrieve array of file names from file.
     *
     * @param string $file
     *
     * @return array Array with files that should be ignored
     */
    private static function getIgnoredFiles($file)
    {
        if (file_exists($file) === false) {
            return [];
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            return [];
        }

        return preg_split('/\s+/', $contents);
    }

    /**
     * Recursive function that makes sure the specified directory exists and
     * is writable.
     *
     * @param string $path
     *
     * @return bool
     */
    private static function dirReady($path)
    {
        if (!file_exists($path)) {
            $parent = preg_replace("/\/[^\/]+\/?$/", '', $path);
            if (!self::dirReady($parent)) {
                return false;
            }

            mkdir($path, 0777, true);
        }

        if (!is_dir($path)) {
            trigger_error('Path is not a directory '.$path, E_USER_WARNING);

            return false;
        }

        if (!is_writable($path)) {
            trigger_error('Unable to write to '.$path.' – check directory permissions –', E_USER_WARNING);

            return false;
        }

        return true;
    }

    /**
     * Easy helper function for retrieving the editor path.
     *
     * @return string Path to editor files
     */
    private function getEditorPath()
    {
        return $this->path.'/editor';
//        return ($this->alteditorpath !== NULL ? $this->alteditorpath : "{$this->path}/editor");
    }

    /**
     * Store the given stream into the given file.
     *
     * @param string   $path
     * @param string   $file
     * @param resource $stream
     *
     * @return bool
     */
    public function saveFileFromZip($path, $file, $stream)
    {
        return true;
    }
}
