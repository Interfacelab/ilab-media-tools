<?php

use Imgix\UrlHelper;

class UrlHelperTest extends PHPUnit_Framework_TestCase {

    public function testHelperBuildSignedURLWithHashMapParams() {
        $params = array("w" => 500);
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "http", "Q61NvXIy", $params);

        $this->assertEquals("http://securejackangers.imgix.net/chester.png?w=500&s=0ddf97bf1a266a1da6c30c6ce327f917", $uh->getURL());
    }

    public function testHelperBuildSignedURLWithHashMapAndNoParams() {
        $params = array();
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "http", "Q61NvXIy", $params);

        $this->assertEquals("http://securejackangers.imgix.net/chester.png?&s=711dfe95b041008a3c6f460a40052282", $uh->getURL());
    }

    public function testHelperBuildSignedURLWithHashSetterParams() {
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "http", "Q61NvXIy");
        $uh->setParameter("w", 500);
        $this->assertEquals("http://securejackangers.imgix.net/chester.png?w=500&s=0ddf97bf1a266a1da6c30c6ce327f917", $uh->getURL());
    }

    public function testHelperBuildSignedURLWithHashSetterParamsHttps() {
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "https", "Q61NvXIy");
        $uh->setParameter("w", 500);
        $this->assertEquals("https://securejackangers.imgix.net/chester.png?w=500&s=0ddf97bf1a266a1da6c30c6ce327f917", $uh->getURL());
    }
}

?>