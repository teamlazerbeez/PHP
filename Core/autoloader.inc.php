<?php
spl_autoload_register(array('gosAutoload', 'autoload'));

class gosAutoload {
    // Directories where we can find class files
    private static $libDirs = array();

    /**
     * Used to try to find and load class files for classes that are attempted to
     * be used but are not found.
     *
     * This searches each of the top-level directories in Genius Open Source for
     * those that contain lib/ or test/ subdirectories.  Each subdirectory is,
     * in turn, searched for the full class path, with the class name's
     * underscores replaced with slashes.
     *
     * Example:
     * gosTest_Fixture_Parser becomes gosTest/Fixture/Parser.(cls|acls|itf).php
     * and the autoloader searches in every lib/ and test/ directory for that
     * file and require()s it, subsequently checking to make sure the class
     * has been loaded.
     *
     * @param string $className Classname to include
     */
    public static function autoload($className) {
        // Pull all of the possible places to find class into our cache
        if (count(self::$libDirs) === 0) {
            self::$libDirs = glob(GOS_ROOT . '*/lib');
            self::$libDirs = array_merge(self::$libDirs, glob(GOS_ROOT . '*/test'));
        }

        // Turn the class name into a path (below one of the lib/ or test/ directories)
        $classPath = str_replace('_', '/', $className);

        $classExt         = '.cls.php';
        $abstractClassExt = '.acls.php';
        $interfaceExt     = '.itf.php';
        $extensions = array($classExt, $abstractClassExt, $interfaceExt);

        // Check each of the lib directories
        foreach (self::$libDirs as $sourceDir) {
            $fullClassPath = $sourceDir .'/'. $classPath;

            // Check each of the extensions
            foreach ($extensions as $extension) {
                $sourcePath = $fullClassPath . $extension;

                if (file_exists($sourcePath)) {
                    // use require instead of require_once to avoid r_o's overhead
                    require($sourcePath);

                    // If the class / interface doesn't now exist, something's wrong...
                    if (!class_exists($className, false) &&
                        !interface_exists($className, false)) {
                        throw new gosException('Found the source file, but it did not '.
                            'contain the definition of ' . $className, get_defined_vars());
                    }

                    break;
                }
            }
        }
    }
}
