<?php

namespace app\admin\library\xshop;

use fast\FormBuilder as Builder;

class Form
{

    /**
     * @param $name
     * @param $arguments
     * @return FormBuilder
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([FormBuilder::instance(), $name], $arguments);
    }
}

class FormBuilder extends Builder
{

    protected $layout = '';

    protected $itemLayout = '';

    protected $defaultArgumentsMap = ['name', 'value' => null, 'options' => []];

    protected $argumentsMaps = [
        'escape' => ['value'],
        'token' => ['name' => '__token__', 'type' => 'md5'],
        'input' => ['type', 'name', 'value' => null, 'options' => []],
        'password' => ['name', 'options' => []],
        'file' => ['name', 'options' => []],
        'slider' => ['name', 'min', 'max', 'step', 'value' => null, 'options' => []],
        'select' => ['name', 'list' => [], 'selected' => null, 'options' => []],
        'selects' => ['name', 'list' => [], 'selected' => null, 'options' => []],
        'selectpicker' => ['name', 'list' => [], 'selected' => null, 'options' => []],
        'selectpickers' => ['name', 'list' => [], 'selected' => null, 'options' => []],
        'selectpage' => ['name', 'value', 'url', 'field' => null, 'primaryKey' => null, 'selected' => null, 'options' => []],
        'selectpages' => ['name', 'value', 'url', 'field' => null, 'primaryKey' => null, 'selected' => null, 'options' => []],
        'fieldlist' => ['name', 'value', 'title' => null, 'template' => null, 'options' => []],
        'cxselect' => ['url', 'names' => [], 'values' => [], 'options' => []],
        'selectRange' => ['name', 'begin', 'end', 'selected' => null, 'options' => []],
        'selectYear' => ['name', 'begin', 'end', 'selected', 'options'],
        'selectMonth' => ['name', 'selected' => null, 'options' => [], 'format' => '%m'],
        'checkbox' => ['name', 'value' => 1, 'checked' => null, 'options' => []],
        'checkboxs' => ['name', 'list', 'checked', 'options' => []],
        'radio' => ['name', 'value', 'checked', 'options' => []],
        'radios' => ['name', 'list', 'value', 'checked' => null, 'options' => []],
        'image' => ['name' => null, 'value', 'inputAttr' => [], 'uploadAttr' => [], 'chooseAttr' => [], 'previewAttr' => []],
        'images' => ['name' => null, 'value', 'inputAttr' => [], 'uploadAttr' => [], 'chooseAttr' => [], 'previewAttr' => []],
        'upload' => ['name' => null, 'value', 'inputAttr' => [], 'uploadAttr' => [], 'chooseAttr' => [], 'previewAttr' => []],
        'uploads' => ['name' => null, 'value', 'inputAttr' => [], 'uploadAttr' => [], 'chooseAttr' => [], 'previewAttr' => []],
        'button' => ['value' => null, 'options' => []]
    ];

    /**
     * 设置Form默认布局
     */
    public function setLayout($html)
    {
        if (is_file($html)) {
            $html = file_get_contents($html);
        }
        $this->layout = $html;
        return $this;
    }

    /**
     * 设置FormItem默认布局
     */
    public function setItemLayout($html)
    {
        $this->itemLayout = $this->getItemLayout($html);
        return $this;
    }

    public function getItemLayout($html = null)
    {
        if (is_file($html)) {
            $html = file_get_contents($html);
        }
        return $html;
    }

    public function create($options = [])
    {
        $this->options = $options;
        return $this;
    }

    public function renderItem($option, $layout = null)
    {
        $args = $this->getArgements($option, $this->argumentsMaps[$option['render']] ?? $this->defaultArgumentsMap);
        $method = $option['render'];
        $html = $this->$method(...$args);
        if ($layout !== false) {
            $html = str_replace('__ITEM_RENDER__', $html, $this->itemLayout);
            if (isset($option['label'])) {
                $html = str_replace('__LABEL_RENDER__', __($option['label']), $html);
            }
        }
        return $html;
    }

    public function render($tag = '__FORM_RENDER__')
    {
        $html = "";
        foreach ($this->options as $option) {
            $html .= $this->renderItem($option);
        }
        return str_replace($tag, $html, $this->layout);
    }

    public function getArgements($option, $fields)
    {
        $args = [];
        foreach ($fields as $k => $v) {
            $field = is_numeric($k) ? $v : $k;
            $defaultValue = is_numeric($k) ? null : $v;
            $args[] = $option[$field] ?? $defaultValue;
        }
        return $args;
    }

    protected function uploader($name = null, $value, $inputAttr = [], $uploadAttr = [], $chooseAttr = [], $previewAttr = [])
    {
        $domname = str_replace(['[', ']', '.'], '', $name);
        $options = [
            'id'            => "plupload-{$domname}",
            'class'         => "btn btn-danger plupload",
            'data-input-id' => "c-{$domname}",
        ];
        $upload = $uploadAttr === false ? false : true;
        $choose = $chooseAttr === false ? false : true;
        $preview = $previewAttr === false ? false : true;
        if ($preview) {
            $options['data-preview-id'] = "p-{$domname}";
        }
        $uploadBtn = $upload ? $this->button('<i class="fa fa-upload"></i> ' . __('Upload'), array_merge($options, $uploadAttr)) : '';
        $options = [
            'id'            => "fachoose-{$domname}",
            'class'         => "btn btn-primary fachoose",
            'data-input-id' => "c-{$domname}",
        ];
        if ($preview) {
            $options['data-preview-id'] = "p-{$domname}";
        }
        $chooseBtn = $choose ? $this->button('<i class="fa fa-list"></i> ' . __('Choose'), array_merge($options, $chooseAttr)) : '';
        $previewAttrHtml = $this->attributes($previewAttr);
        $previewArea = $preview ? '<ul class="row list-inline plupload-preview" id="p-' . $domname . '" ' . $previewAttrHtml . '></ul>' : '';
        $input = $this->text($name, $value, array_merge(['size' => 50, 'id' => "c-{$domname}"], $inputAttr));
        $html = <<<EOD
<div class="input-group">
                {$input}
                <div class="input-group-addon no-border no-padding">
                    <span>{$uploadBtn}</span>                  
                    <span>{$chooseBtn}</span>
                </div>
                <span class="msg-box n-right" for="c-{$domname}"></span>
            </div>
            {$previewArea}
EOD;
        return $html;
    }
}