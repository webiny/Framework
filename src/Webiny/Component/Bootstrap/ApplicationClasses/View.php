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
    private $title;

    /**
     * @var string Path to the template file.
     */
    private $template;

    /**
     * @var array List of script files.
     */
    private $scripts = [];

    /**
     * @var array List of stylesheets.
     */
    private $styles = [];

    /**
     * @var array List of html meta data.
     */
    private $meta = [];

    /**
     * @var array Assigned view data.
     */
    private $viewData = [];

    /**
     * @var bool Should the view file be auto loaded or not.
     */
    private $autoloadViewTemplate = true;


    /**
     * Set the page title.
     *
     * @param string $title Page title.
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
        $this->scripts[] = [
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
        array_unshift($this->scripts, [
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
        $this->styles[] = $path;

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
        array_unshift($this->styles, $path);

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
        $this->meta[$name] = $content;

        return $this;
    }

    /**
     * Get the current page title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the current page title as html tag.
     *
     * @return string
     */
    public function getTitleHtml()
    {
        return '<title>' . $this->title . '</title>';
    }

    /**
     * Get the script list.
     *
     * @return array
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Get the script list as html tags.
     *
     * @return string
     */
    public function getScriptsHtml()
    {
        $scripts = '';
        if (!isset($this->scripts)) {
            return $scripts;
        }

        foreach ($this->scripts as $s) {
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
        return $this->styles;
    }

    /**
     * Get the stylesheet list as html tags.
     *
     * @return string
     */
    public function getStyleSheetsHtml()
    {
        $styleSheets = '';
        if (!isset($this->styles)) {
            return $styleSheets;
        }

        foreach ($this->styles as $s) {
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
        return $this->meta;
    }

    /**
     * Get the meta list as html tags.
     *
     * @return string
     */
    public function getMetaHtml()
    {
        $meta = '';
        if (empty($this->meta)) {
            return $meta;
        }

        foreach ($this->meta as $name => $content) {
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
        $this->template = $template;
    }

    /**
     * Get the assigned view template.
     *
     * @return string Path to the view template.
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Assign variables to the view.
     *
     * @param array  $data List of variables that should be assigned.
     * @param string $root Variable root. Default root is Ctrl.
     */
    public function assign(array $data, $root = 'Ctrl')
    {
        foreach ($data as $k => $d) {
            if ($root != '') {
                if (!isset($this->viewData[$root])) {
                    $this->viewData[$root] = [];
                }
                $this->viewData[$root][$k] = $d;
            } else {
                $this->viewData[$k] = $d;
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
        $this->viewData['View'] = [
            'Title'           => $this->getTitle(),
            'TitleHtml'       => $this->getTitleHtml(),
            'Scripts'         => $this->getScripts(),
            'ScriptsHtml'     => $this->getScriptsHtml(),
            'StyleSheets'     => $this->getStyleSheets(),
            'StyleSheetsHtml' => $this->getStyleSheetsHtml(),
            'Meta'            => $this->getMeta(),
            'MetaHtml'        => $this->getMetaHtml()
        ];

        return $this->viewData;
    }

    /**
     * Set the view template auto load.
     *
     * @param bool $autoload Should the view template file be auto loaded or not.
     */
    public function setAutoload($autoload)
    {
        $this->autoloadViewTemplate = $autoload;
    }

    /**
     * Get the state of view template auto load.
     *
     * @return bool
     */
    public function getAutoload()
    {
        return $this->autoloadViewTemplate;
    }
}