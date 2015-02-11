<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\ApplicationClasses;

/**
 * View class
 *
 * @package         Webiny\Component\Bootstrap\ApplicationClasses;
 */

class View
{
    /**
     * @var string Page title.
     */
    private $_title;

    /**
     * @var string Path to the template file.
     */
    private $_template;

    /**
     * @var array List of script files.
     */
    private $_scripts = [];

    /**
     * @var array List of stylesheets.
     */
    private $_styles = [];

    /**
     * @var array List of html meta data.
     */
    private $_meta = [];

    /**
     * @var array Assigned view data.
     */
    private $_viewData = [];

    /**
     * @var bool Should the view file be auto loaded or not.
     */
    private $_autoloadViewTemplate = true;


    /**
     * Set the page title.
     *
     * @param string $title Page title.
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    /**
     * Append a script to the script array.
     *
     * @param string $path Path to the script. It can be relative path to the Public/static folder or a full http path.
     * @param string $type Script type, default is 'text/javascript'.
     *
     * @return $this
     */
    public function appendScript($path, $type = 'text/javascript')
    {
        $this->_scripts[] = [
            'path' => $path,
            'type' => $type
        ];

        return $this;
    }

    /**
     * Prepends a script to the script array.
     *
     * @param string $path Path to the script. It can be relative path to the Public/static folder or a full http path.
     * @param string $type Script type, default is 'text/javascript'.
     *
     * @return $this
     */
    public function prependScript($path, $type = 'text/javascript')
    {
        array_unshift($this->_scripts, [
                                         'path' => $path,
                                         'type' => $type
                                     ]
        );

        return $this;
    }

    /**
     * Appends a stylesheet to the stylesheet array.
     *
     * @param string $path Path to the stylesheet. It can be relative path to the Public/static folder or a full http path.
     *
     * @return $this
     */
    public function appendStyleSheet($path)
    {
        $this->_styles[] = $path;

        return $this;
    }

    /**
     * Prepends a stylesheet to the stylesheet array.
     *
     * @param string $path Path to the stylesheet. It can be relative path to the Public/static folder or a full http path.
     *
     * @return $this
     */
    public function prependStyleSheet($path)
    {
        array_unshift($this->_styles, $path);

        return $this;
    }

    /**
     * Set a html meta tag.
     *
     * @param string $name    Meta name.
     * @param string $content Meta value.
     *
     * @return $this
     */
    public function setMeta($name, $content)
    {
        $this->_meta[$name] = $content;

        return $this;
    }

    /**
     * Get the current page title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Get the current page title as html tag.
     *
     * @return string
     */
    public function getTitleHtml()
    {
        return '<title>' . $this->_title . '</title>';
    }

    /**
     * Get the script list.
     *
     * @return array
     */
    public function getScripts()
    {
        return $this->_scripts;
    }

    /**
     * Get the script list as html tags.
     *
     * @return string
     */
    public function getScriptsHtml()
    {
        $scripts = '';
        if (!isset($this->_scripts)) {
            return $scripts;
        }

        foreach ($this->_scripts as $s) {
            $scripts .= '<script type="' . $s['type'] . '" src="' . $s['path'] . '"></script>' . "\n";
        }

        return $scripts;
    }

    /**
     * Get the stylesheet list.
     *
     * @return array
     */
    public function getStyleSheets()
    {
        return $this->_styles;
    }

    /**
     * Get the stylesheet list as html tags.
     *
     * @return string
     */
    public function getStyleSheetsHtml()
    {
        $styleSheets = '';
        if (!isset($this->_styles)) {
            return $styleSheets;
        }

        foreach ($this->_styles as $s) {
            $styleSheets .= '<link rel="stylesheet" type="text/css" href="' . $s . '"/>' . "\n";
        }

        return $styleSheets;
    }

    /**
     * Get the meta list.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->_meta;
    }

    /**
     * Get the meta list as html tags.
     *
     * @return string
     */
    public function getMetaHtml()
    {
        $meta = '';
        if (empty($this->_meta)) {
            return $meta;
        }

        foreach ($this->_meta as $name => $content) {
            $meta .= '<meta name="' . $name . '" content="' . $content . '"/>' . "\n";
        }

        return $meta;
    }

    /**
     * Set the view template file.
     *
     * @param string $template Path to template file.
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Get the assigned view template.
     *
     * @return string Path to the view template.
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Assign variables to the view.
     *
     * @param array  $data List of variables that should be assigned.
     * @param string $root Variable root. Default root is Ctrl.
     */
    public function assign(array $data, $root = 'Ctrl')
    {
        foreach ($data as $k => &$d) {
            if ($root != '') {
                if (!isset($this->_viewData[$root])) {
                    $this->_viewData[$root] = [];
                }
                $this->_viewData[$root][$k] = $d;
            } else {
                $this->_viewData[$k] = $d;
            }

        }
    }

    /**
     * Get a list of assigned view data.
     *
     * @return array
     */
    public function getAssignedData()
    {
        // append the internal data to the view
        $this->_viewData['View'] = [
            'Title'           => $this->getTitle(),
            'TitleHtml'       => $this->getTitleHtml(),
            'Scripts'         => $this->getScripts(),
            'ScriptsHtml'     => $this->getScriptsHtml(),
            'StyleSheets'     => $this->getStyleSheets(),
            'StyleSheetsHtml' => $this->getStyleSheetsHtml(),
            'Meta'            => $this->getMeta(),
            'MetaHtml'        => $this->getMetaHtml()
        ];

        return $this->_viewData;
    }

    /**
     * Set the view template auto load.
     *
     * @param bool $autoload Should the view template file be auto loaded or not.
     */
    public function setAutoload($autoload)
    {
        $this->_autoloadViewTemplate = $autoload;
    }

    /**
     * Get the state of view template auto load.
     *
     * @return bool
     */
    public function getAutoload()
    {
        return $this->_autoloadViewTemplate;
    }
}