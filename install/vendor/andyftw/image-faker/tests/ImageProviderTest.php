<?php

namespace Andyftw\Tests\Faker;

class ImageProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->faker = \Faker\Factory::create();
        $this->faker->addProvider(new \Andyftw\Faker\ImageProvider($this->faker));

        $this->files = null;
    }

    /**
     * Clean up any temporary images.
     */
    public function tearDown()
    {
        if ($this->files !== null) {
            foreach ($this->files as $f) {
                @unlink($f);
            }
        }
    }

    private function _testImage($test)
    {
        $this->assertNotNull(@exif_imagetype($test));
    }

    /**
     * Test creating an image with the default setup.
     */
    public function testCreateDefaultImage()
    {
        $this->files[] = $test = $this->faker->imageFile();
        $this->_testImage($test);
    }

    /**
     * Test using a invalid directory, /dev/null.
     */
    public function testInvalidDirectory()
    {
        try {
            $this->faker->imageFile('/dev/null');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Cannot write to directory "/dev/null"');
        }
    }

    /**
     * Test using a invalid hex color, #1234.
     */
    public function testInvalidColorHex()
    {
        try {
            $this->faker->imageFile(null, 640, 480, 'png', true, null, '#1234');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Unrecognized hexcolor "#1234"');
        }
    }

    /**
     * Test using a font file.
     */
    // public function testFontFile()
    // {
    //     $font = __DIR__.'/../res/OpenSans-Regular.ttf';
    //     $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'png', true, null, null, null, $font);
    //     $this->_testImage($test);
    // }

    /**
     * Test using text using a color without a hex.
     */
    public function testTextColorHex()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'png', true, null, '#0000ff');
        $this->_testImage($test);
    }

    /**
     * Test using text using a color with a hex.
     */
    public function testTextColorHexNoHash()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'png', true, null, '0000ff');
        $this->_testImage($test);
    }

    /**
     * Test using a background color with a hex.
     */
    public function testBackgroundColorHex()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'png', true, null, null, '#0000ff');
        $this->_testImage($test);
    }

    /**
     * Test using a background color without a hex.
     */
    public function testBackgroundColorHexNoHash()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'png', true, null, null, '0000ff');
        $this->_testImage($test);
    }

    /**
     * Test showing text over the image.
     */
    public function testText()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'png', true, '%width%x%height%');
        $this->_testImage($test);
    }

    /**
     * Test creating a image with an extention of .jpg.
     */
    public function testJPG()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'jpg');
        $this->_testImage($test);
    }

    /**
     * Test creating a image with an extention of .jpeg.
     */
    public function testJPEG()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'jpeg');
        $this->_testImage($test);
    }

    /**
     * Test creating a image with an extention of .png.
     */
    public function testPNG()
    {
        $this->files[] = $test = $this->faker->imageFile(null, 640, 480, 'png');
        $this->_testImage($test);
    }
}
