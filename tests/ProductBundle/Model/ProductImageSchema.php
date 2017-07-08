<?php
namespace ProductBundle\Model;
use Maghead\Schema\DeclareSchema;

class ProductImageSchema extends DeclareSchema
{

    public function getLabel() {
        return '產品圖';
    }

    public function schema()
    {
        $this->column( 'product_id' )
            ->integer()
            ->refer('ProductBundle\\Model\\Product')
            ->renderAs('SelectInput')
            ->label('產品');

        $this->column( 'title' )
            ->varchar(130)
            ->label('圖片標題');

        $this->column('image')
            ->varchar(130)
            ->required()
            ->contentType('ImageFile')
            ->label('圖')
            ->buildParam(function($param) {
                return $param
                    ->sizeLimit(1024 * 1024 * 10) // 10MB
                    ->size([ 'width' => 120, 'height' => 120 ])
                    ->autoResize(true)
                    ->putIn('tests/upload')
                    ;
            })
            ;

        $this->column('large')
            ->varchar(130)
            ->contentType('ImageFile')
            ->label('最大圖')
            ->buildParam(function($param) {
                return $param->sizeLimit(1024 * 1024 * 10) // 10MB
                    ->size([ 'width' => 120, 'height' => 120 ])
                    ->autoResize(true)
                    ->putIn('tests/upload')
                    ;
            })
            ;
    }
}


