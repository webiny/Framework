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

    private $_title;
    private $_scripts;
    private $_styles;
    private $_meta;
    private $_template;
    private $_viewData;
    private $_autoloadViewTemplate = true;


    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    public function appendScript($path, $type = 'text/javascript')
    {
        $this->_scripts[] = [
            'path' => $path,
            'type' => $type
        ];

        return $this;
    }

    public function prependScript($path, $type = 'text/javascript')
    {
        array_unshift($type->_scripts, [
                                         'path' => $path,
                                         'type' => $type
                                     ]
        );

        return $this;
    }

    public function appendStyleSheet($path)
    {
        $this->_styles[] = $path;

        return $this;
    }

    public function prependStyleSheet($path)
    {
        array_unshift($this->_styles[], $path);

        return $this;
    }

    public function setMeta($name, $content)
    {
        $this->_meta[$name] = $content;

        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getTitleHtml()
    {
        return '<title>' . $this->_title . '</title>';
    }

    public function getScripts()
    {
        return $this->_scripts;
    }

    public function getScriptsHtml()
    {
        $scripts = '';
        if (!isset($this->_scripts)) {
            return $scripts;
        }

        foreach ($this->_scripts as $s) {
            $scripts .= '<script type="' . $s['type'] . '" src="' . $s['path'] . '"></script>'."\n";
        }

        return $scripts;
    }

    public function getStyleSheets()
    {
        return $this->_styles;
    }

    public function getStyleSheetsHtml()
    {
        $styleSheets = '';
        if (!isset($this->_styles)) {
            return $styleSheets;
        }

        foreach ($this->_styles as $s) {
            $styleSheets .= '<link rel="stylesheet" type="text/css" href="' . $s . '"/>'."\n";
        }

        return $styleSheets;
    }

    public function getMeta()
    {
        return $this->_meta;
    }

    public function getMetaHtml()
    {
        $meta = '';
        if (empty($this->_meta)) {
            return $meta;
        }

        foreach ($this->_meta as $name => $content) {
            $meta .= '<meta name="' . $name . '" content="' . $content . '"/>'."\n";
        }

        return $meta;
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    public function assign(array $data, $root = 'ctrl')
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

    public function getAssignedData()
    {
        // append the internal data to the view
        $this->_viewData['View'] = [
            'Title'             => $this->getTitle(),
            'TitleHtml'         => $this->getTitleHtml(),
            'Scripts'           => $this->getScripts(),
            'ScriptsHtml'       => $this->getScriptsHtml(),
            'StyleSheets'       => $this->getStyleSheets(),
            'StyleSheetsHtml'   => $this->getStyleSheetsHtml(),
            'Meta'              => $this->getMeta(),
            'MetaHtml'          => $this->getMetaHtml()
        ];

        return $this->_viewData;
    }

    public function getTemplate()
    {
        return $this->_template;
    }

    public function setAutoload($autoload)
    {
        $this->_autoloadViewTemplate = $autoload;
    }

    public function getAutoload()
    {
        return $this->_autoloadViewTemplate;
    }
}