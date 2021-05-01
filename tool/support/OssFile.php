<?php

namespace tool\support;

use beacon\core\Form;
use beacon\widget\Number;
use beacon\widget\Text;
use beacon\widget\RadioGroup;
use tool\libs\Support;

#[Support(name: 'OSS文件上传', types: ['varchar(300)', 'json', 'text'])]
#[Form(title: 'OSS文件上传', template: 'form/field_support.tpl')]
class OssFile
{

    #[RadioGroup(
        label: '上传模式',
        options: [
        ['file', '单文件'],
        ['fileGroup', '多文件'],
    ],
        dynamic: [
            [
                'eq' => 'file',
                'show' => 'nameInput',
                'hide' => 'size',
            ],
            [
                'eq' => 'fileGroup',
                'show' => 'size',
                'hide' => 'nameInput',
            ],
        ]
    )]
    public string $mode = 'file';

    #[Text(
        label: '支持的文件类型',
        attrs: [
            'style' => 'width:400px'
        ]
    )]
    public string $extensions = 'txt,doc,docx,zip,rar,jpg,jpeg,png,bmp,gif,xls,xlsx,pdf';

    #[Number(
        label: '上传最大数量',
        prompt: '0为不限制数量',
    )]
    public int $size = 0;

    #[Text(
        label: '文件名写入控件名',
        prompt: '如果设置，上传后文件名将回写到您设置的输入框',
    )]
    public string $nameInput = '';

    public function export(): array
    {
        return [
            'mode' => $this->mode,
            'extensions' => $this->extensions,
            'size' => $this->size,
            'nameInput' => $this->nameInput,
        ];
    }
}