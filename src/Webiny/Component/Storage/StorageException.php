<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */

namespace Webiny\Component\Storage;

use Webiny\Component\StdLib\Exception\AbstractException;

/**
 * Storage exception class
 *
 * @package      Webiny\Bridge\Storage
 */
class StorageException extends AbstractException
{

    const FILE_NOT_FOUND = 101;
    const FAILED_TO_READ = 102;
    const DRIVER_DOES_NOT_SUPPORT_TOUCH = 103;
    const DRIVER_CAN_NOT_ACCESS_SIZE = 104;
    const DRIVER_CAN_NOT_WORK_WITH_DIRECTORIES = 105;
    const DRIVER_DOES_NOT_SUPPORT_ABSOLUTE_PATHS = 106;
    const FILE_OBJECT_CAN_NOT_READ_DIRECTORY_PATHS = 107;
    const DIRECTORY_DOES_NOT_EXIST = 108;
    const DIRECTORY_COULD_NOT_BE_CREATED = 109;
    const DIRECTORY_OBJECT_CAN_NOT_READ_FILE_PATHS = 110;
    const PATH_IS_OUT_OF_STORAGE_ROOT = 111;

    protected static $messages = [
        101 => 'File not found!',
        102 => 'Failed to read file! Make sure the file exists and you have permissions to access it!',
        103 => 'Storage driver `%s` does not support touching of files!',
        104 => 'Storage driver `%s` can not access file size info!',
        105 => 'Storage driver `%s` can not work with directories!',
        106 => 'Storage driver `%s` does not support absolute paths!',
        107 => 'File object can not read directory paths (attempting to read `%s`)! Use `\Webiny\Component\Storage\Directory\Directory` class instead.',
        108 => 'Storage directory `%s` does not exist!',
        109 => 'Storage directory `%s` could not be created!',
        110 => 'Directory object can not read file paths (attempting to read `%s`)!',
        111 => 'The path `%s` is out of storage root `%s`.'
    ];
}