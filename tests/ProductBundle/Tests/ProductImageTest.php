<?php

namespace ProductBundle\Tests;

use WebAction\ActionRunner;
use WebAction\ActionRequest;
use WebAction\ActionLoader;
use WebAction\ActionGenerator;
use WebAction\DefaultConfigurations;
use WebAction\ActionTemplate\TwigActionTemplate;
use WebAction\ActionTemplate\SortRecordActionTemplate;
use WebAction\ActionTemplate\RecordActionTemplate;
use WebAction\RecordAction\UpdateRecordAction;
use WebAction\Param\Param;
use WebAction\Result;

use WebAction\Testing\ActionTestCase;
use WebAction\Testing\ActionTestAssertions;

use Maghead\Testing\ModelTestCase;


use ProductBundle\Model\ProductSchema;
use ProductBundle\Model\ProductFeatureSchema;
use ProductBundle\Model\ProductCategorySchema;
use ProductBundle\Model\ProductProductSchema;
use ProductBundle\Model\ProductFileSchema;
use ProductBundle\Model\CategorySchema;
use ProductBundle\Model\FeatureSchema;
use ProductBundle\Model\ProductImageSchema;

use ProductBundle\Model\Product;
use ProductBundle\Model\ProductImage;
use ProductBundle\Model\ProductImageCollection;

use ProductBundle\Action\CreateProduct;
use ProductBundle\Action\UpdateProduct;

use ProductBundle\Action\CreateProductImage;
use ProductBundle\Action\UpdateProductImage;


class CustomCreateProductImageAction extends CreateProductImage {

}


/**
 * @group maghead
 */
class ProductImageTest extends ModelTestCase
{
    use ActionTestAssertions;

    public function models()
    {
        return [
            new ProductSchema,
            new CategorySchema,
            new ProductCategorySchema,
            new ProductImageSchema,
            new ProductFeatureSchema,
            new FeatureSchema,
            new ProductProductSchema,
            new ProductFileSchema,
        ];
    }

    public function testProductCreateWithCustomProductImageSubAction()
    {
        $tmpfile = tempnam('/tmp', 'test_image_');
        copy('tests/data/404.png', $tmpfile);
        $files = [
            'images' => $this->createFilesArrayWithAssociateKey([
                'a' => [ 'image' => $this->createFileArray('404.png', 'image/png', $tmpfile) ], 
                'b' => [ 'image' => $this->createFileArray('404.png', 'image/png', $tmpfile) ], 
            ]),
        ];
        $args = ['name' => 'Test Product', 'images' => [ 
            // files are in another array
            'a' => [ ],
            'b' => [ ],
        ]];

        $request = new ActionRequest($args, $files);

        $create = new CreateProduct($args, ['request' => $request ]);

        $relation = clone $create->getRelation('images');
        $relation['action'] = CustomCreateProductImageAction::class;

        $create->addRelation('images', $relation);

        $result = $this->assertActionInvokeSuccess($create, $request);

        $product = $create->getRecord();
        $this->assertNotNull($product);
        $this->assertNotNull($product->id, 'product created');

        $images = $product->images;
        $this->assertCount(2, $images);
    }


    /**
     * XXX: add test details later
     */
    public function testConvertRecordValidation()
    {
        $ret = ProductImage::create([]);

        $create = new CreateProductImage;
        $create->convertRecordValidation($ret);

        $result = $create->getResult();
        $data = $result->data;
    }

    public function testProductCreateWithProductImageSubAction()
    {
        $tmpfile = tempnam('/tmp', 'test_image_');
        copy('tests/data/404.png', $tmpfile);
        $files = [
            'images' => $this->createFilesArrayWithAssociateKey([
                'a' => [ 'image' => $this->createFileArray('404.png', 'image/png', $tmpfile) ], 
                'b' => [ 'image' => $this->createFileArray('404.png', 'image/png', $tmpfile) ], 
            ]),
        ];
        $args = ['name' => 'Test Product', 'images' => [ 
            // files are in another array
            'a' => [ ],
            'b' => [ ],
        ]];
        $request = new ActionRequest($args, $files);
        $create = new CreateProduct($args, [ 'request' => $request ]);
        $result = $this->assertActionInvokeSuccess($create, $request);

        $product = $create->getRecord();
        $this->assertNotNull($product);
        $this->assertNotNull($product->id, 'product created');

        $images = $product->images;
        $this->assertCount(2, $images);
        foreach($images as $image) { $image->delete(); }
    }

    public function testProductCreateSubActionWithCreateProductImage()
    {
        $files = [ ];
        $request = new ActionRequest(['name' => 'Test Product'], $files);
        $ret = Product::create([
            'name' => 'Testing Product',
        ]);
        $this->assertResultSuccess($ret);

        $product = Product::load($ret->key);
        $this->assertNotNull($product->id);
        $create = new CreateProduct(['name' => 'Test Product'], [ 'request' => $request, 'record' => $product ]);
        $createImage = $create->createSubAction('images', [ ]);
        $this->assertNotNull($createImage);
    }

}
