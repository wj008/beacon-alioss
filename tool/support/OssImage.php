<?php

namespace tool\support;

use beacon\core\Form;
use beacon\widget\Number;
use beacon\widget\Text;
use beacon\widget\RadioGroup;
use tool\libs\Support;

#[Support(name: 'OSS图片上传', types: ['varchar(300)', 'json', 'text'])]
#[Form(title: 'OSS图片上传', template: 'form/field_support.tpl')]
class OssImage
{
    #[RadioGroup(
        label: '上传模式',
        options: [
        ['image', '单图'],
        ['imgGroup', '多图'],
    ],
        dynamic: [
            [
                'eq' => 'image',
                'hide' => 'size',
            ],
            [
                'eq' => 'imgGroup',
                'show' => 'size',
            ],
        ]
    )]
    public string $mode = 'image';

    #[Text(
        label: '支持的图片类型',
        attrs: [
            'style' => 'width:400px'
        ]
    )]
    public string $extensions = 'jpg,jpeg,bmp,gif,png';

    #[Number(
        label: '显示区宽',
    )]
    public int $imgWidth = 0;

    #[Number(
        label: '高',
        viewMerge: -1
    )]
    public int $imgHeight = 0;

    #[Number(
        label: '上传最大数量',
        prompt: '0为不限制数量',
    )]
    public int $size = 0;


    public function export(): array
    {
        return [
            'mode' => $this->mode,
            'extensions' => $this->extensions,
            'imgWidth' => $this->imgWidth,
            'imgHeight' => $this->imgHeight,
            'size' => $this->size,
        ];
    }
}