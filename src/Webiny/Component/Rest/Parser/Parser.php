<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

/**
 * This is the main parser class.
 * This class takes a class name and parses its methods and parameters.
 * The result of parsing is an instance of ParsedClass.
 *
 * @package         Webiny\Component\Annotations\AnnotationsTrait
 */
class Parser
{
    /**
     * Parses the api class and all its versions.
     *
     * @param string $class Fully qualified name of the api class.
     * @param bool   $normalize Should the class name and the method name be normalized.
     *
     * @return ParsedApi
     */
    public function parseApi($class, $normalize)
    {
        $versions = $this->getVersions($class);

        $parsedApi = new ParsedApi($class);
        foreach ($versions['versions'] as $v => $versionClass) {
            $classParser = new ClassParser($versionClass, $normalize);
            $parsedApi->addVersion($classParser->getParsedClass(), $v);
        }

        $parsedApi->setCurrentVersion($versions['current']);
        $parsedApi->setLatestVersion($versions['latest']);

        return $parsedApi;
    }

    /**
     * Returns a list of supported versions for the given $class.
     *
     * @param string $class Fully qualified class name.
     *
     * @return array
     */
    private function getVersions($class)
    {
        $versions = [
            'versions' => [
                '1.0' => $class
            ],
            'latest'   => '1.0',
            'current'  => '1.0'
        ];

        if (method_exists($class, 'getAllVersions')) {
            // all versions
            $interfaceVersions = $class::getAllVersions();
            if (count($interfaceVersions) > 0) {
                $versions = ['versions' => $interfaceVersions];
            }

            // current
            $interfaceCurrent = $class::getCurrentVersion();
            if (!empty($interfaceCurrent)) {
                $versions['current'] = $interfaceCurrent;
            } else {
                $versions['current'] = end(array_keys($versions['versions']));
            }

            // latest
            $interfaceLatest = $class::getLatestVersion();
            if (!empty($interfaceLatest)) {
                $versions['latest'] = $interfaceLatest;
            } else {
                $versions['latest'] = end(array_keys($versions['versions']));
            }
        }

        return $versions;
    }
}