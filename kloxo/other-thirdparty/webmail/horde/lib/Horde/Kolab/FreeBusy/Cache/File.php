<?php
/**
 * A representation of a cache file.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/**
 * A representation of a cache file.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache/File.php,v 1.1.2.3 2010/10/10 16:26:42 wrobel Exp $
 *
 * Copyright 2004-2008 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Cache_File {

    /**
     * The suffix of this cache file.
     *
     * @var string
     */
    private $_suffix = '';

    /**
     * Full path to the cache file.
     *
     * @var string
     */
    private $_file;

    /**
     * Cache file version.
     *
     * @var int
     */
    private $_version = 1;

    /**
     * Construct the Horde_Kolab_FreeBusy_Cache_File instance.
     *
     * @param string $cache_dir The path to the cache direcory.
     * @param string $suffix    The suffix of the cache file name.
     */
    public function __construct(
        $cache_dir
    ) {
        $this->_cache_dir = $cache_dir;
    }

    /**
     * Set the cache file suffix.
     *
     * @param string $suffix The suffix.
     *
     * @return NULL
     */
    protected function setSuffix($suffix)
    {
        $this->_suffix = $suffix;
    }

    /**
     * Set the version expected for the cached data.
     *
     * @param int $version The version number.
     *
     * @return NULL
     */
    protected function setVersion($version)
    {
        $this->_version = $version;
    }

    /**
     * Set the partial represented by this cache file.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Partial $partial The partial.
     *
     * @return NULL
     */
    public function setPartial(
        Horde_Kolab_FreeBusy_Cache_Partial $partial
    ) {
        $this->setFilename($partial->getId());
    }

    /**
     * Set the full path to the cache file.
     *
     * @param string $filename  The file name of the cache file.
     *
     * @return NULL
     */
    public function setFilename($filename)
    {
        if ($this->_suffix === null) {
            throw new Horde_Kolab_FreeBusy_Exception(
                'You need to set the cache file suffix first!'
            );
        }
        $this->_file = $this->_cache_dir . '/' . $filename . '.' . $this->_suffix;
    }

    /**
     * Get the full path to the cache file.
     *
     * @return string The full path to the file.
     */
    public function getFile()
    {
        if ($this->_file === null) {
            throw new Horde_Kolab_FreeBusy_Exception(
                'No partial has been associated with this file cache yet!'
            );
        }
        return $this->_file;
    }

    /**
     * Clean the cache file contents.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case cleaning the cache file
     * failed.
     */
    public function delete()
    {
        if (file_exists($this->getFile())) {
            if (!@unlink($this->getFile())) {
                throw new Horde_Kolab_FreeBusy_Exception(
                    sprintf(
                        "Failed removing file %s: %s",
                        $this->getFile(),
                        $this->_describeLastError()
                    )
                );
            }
        }
    }

    /**
     * Store data in the cache file.
     *
     * @param mixed $data A reference to the data object.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case storing the data failed.
     */
    public function store(&$data)
    {
        /* Create directories if missing */
        $fbdirname = dirname($this->getFile());
        if (!is_dir($fbdirname)) {
            $this->_makeTree($fbdirname);
        }

        /* Store the cache data */
        if (!$fh = fopen($this->getFile(), 'w')) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    "Failed creating cache file %s: %s",
                    $this->getFile(),
                    $this->_describeLastError()
                )
            );
        }
        fwrite(
            $fh,
            serialize(
                array(
                    'version' => $this->_version,
                    'data' => $data)
            )
        );
        fclose($fh);
    }

    /**
     * Load data from the cache file.
     *
     * @return mixed The data retrieved from the cache file.
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case reading the cached data failed.
     */
    public function load()
    {
        if (($file = @file_get_contents($this->getFile())) === false) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    "%s failed reading cache file %s: %s",
                    get_class($this),
                    $this->getFile(),
                    $this->_describeLastError()
                )
            );
        }
        if (!$cache = @unserialize($file)) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    "%s failed to unserialize cache data from file %s!",
                    get_class($this),
                    $this->getFile(),
                    $this->_describeLastError()
                )
            );
        }
        if (!isset($cache['version'])) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    "Cache file %s lacks version data!",
                    $this->getFile()
                )
            );
        }
        $this->_version = $cache['version'];
        if (!isset($cache['data'])) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    "Cache file %s lacks data!",
                    $this->getFile()
                )
            );
        }
        if ($cache['version'] != $this->_version) {
            throw new Horde_Kolab_FreeBusy_Exception(
                sprintf(
                    "Cache file %s has version %s while %s is required!",
                    $this->getFile(),
                    $cache['version'],
                    $this->_version
                )
            );
        }
        return $cache['data'];
    }


    /**
     * Return the last modification date of the cache file.
     *
     * @return int The last modification date.
     */
    public function getMtime()
    {
        if (file_exists($this->getFile())) {
            return filemtime($this->getFile());
        } else {
            return -1;
        }
    }

    /**
     * Generate a tree of directories.
     *
     * @param string $dirname The path to a directory that should exist.
     *
     * @return NULL
     *
     * @throws Horde_Kolab_FreeBusy_Exception In case creating the tree failed.
     */
    private function _maketree($dirname)
    {
        $base = substr($dirname, 0, strrpos($dirname, '/'));
        $base = str_replace(".", "^", $base);
        if (!empty($base) && !is_dir($base)) {
            $this->_maketree($base);
        }
        if (!file_exists($dirname)) {
            if (!@mkdir($dirname, 0755)) {
                throw new Horde_Kolab_FreeBusy_Exception(
                    sprintf(
                        "Error creating directory %s: %s",
                        $dirname,
                        $this->_describeLastError()
                    )
                );
            }
        }
    }

    /**
     * Return a descriptive string for the last error.
     *
     * @return string An error string.
     *
     * @todo Check how to do this with H4 (exceptions).
     */
    private function _describeLastError()
    {
        $error = error_get_last();
        return sprintf(
            '%s [line %s in %s]',
            $error['message'],
            $error['line'],
            $error['file']
        );
    }
}
