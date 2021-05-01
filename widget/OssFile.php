<?php

namespace beacon\widget;

use beacon\core\Config;
use beacon\core\Field;

#[\Attribute]
class OssFile extends Field
{
    public string $mode = 'file';
    public string $extensions = '';
    public string $nameInput = '';
    public int $size = 0;
    /**
     * @var bool 开启文档转换
     */
    public bool $convert = true;

    public function setting(array $args)
    {
        parent::setting($args);
        if (isset($args['mode']) && is_string($args['mode'])) {
            $this->mode = $args['mode'];
        }
        if (isset($args['extensions']) && is_string($args['extensions'])) {
            $this->extensions = $args['extensions'];
        }
        if (isset($args['nameInput']) && is_string($args['nameInput'])) {
            $this->nameInput = $args['nameInput'];
        }
        if (isset($args['size']) && is_int($args['size'])) {
            $this->size = $args['size'];
        }
        if (isset($args['convert']) && is_bool($args['convert'])) {
            $this->convert = $args['convert'];
        }
    }

    protected function code(array $attrs = []): string
    {
        $attrs['yee-module'] = $this->getYeeModule('upload oss');
        $attrs['type'] = 'text';
        $attrs['class'] = 'form-inp up-file';
        $attrs['data-url'] = Config::get('aliyun.oss_url');
        $attrs['data-web-url'] = Config::get('aliyun.oss_web_url');
        $attrs['data-field-name'] = 'file';
        if ($this->convert) {
            $attrs['data-convert'] = 'true';
        }
        if (!empty($this->mode)) {
            $attrs['data-mode'] = $this->mode;
            if ($this->mode == 'fileGroup' && $this->size > 0) {
                $attrs['data-size'] = $this->size;
            }
            if ($this->mode == 'file' && !empty($this->nameInput)) {
                $attrs['data-name-input'] = $this->nameInput;
            }
        }
        if (!empty($this->extensions)) {
            $attrs['data-extensions'] = $this->extensions;
        }
        return static::makeTag('input', ['attrs' => $attrs]);
    }
}