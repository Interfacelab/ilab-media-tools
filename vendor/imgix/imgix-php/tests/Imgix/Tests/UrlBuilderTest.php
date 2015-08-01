<?php

use Imgix\UrlBuilder;
use Imgix\ShardStrategy;

class UrlBuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage UrlBuilder requires at least one domain
     */
    public function testURLBuilderRaisesExceptionOnNoDomains() {
        $domains = array();
        $ub = new URLBuilder($domains);
    }

    public function testUrlBuilderCycleShard() {
        // generate a url for the number of domains in use ensure they're cycled through...

        $domains = array("jackangers.imgix.net", "jackangers2.imgix.net", "jackangers3.imgix.net");

        $ub = new URLBuilder($domains, false, "", ShardStrategy::CRC, false);
        $ub->setShardStrategy(ShardStrategy::CYCLE);

        for ($i = 0; $i < 100; $i++) {
          $used = array();
          foreach ($domains as $domain) {
            $url = $ub->createURL("chester.png");
            $curDomain = parse_url($url)["host"];
            $this->assertFalse(in_array($curDomain, $used));
            $used[] = $curDomain;
          }
        }
    }

    public function testExamplePlain() {
        $builder = new UrlBuilder("demos.imgix.net", false, "", ShardStrategy::CRC, false);

        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("http://demos.imgix.net/bridge.png?h=100&w=100", $url);
    }

    public function testExamplePlainHttps() {
        $builder = new UrlBuilder("demos.imgix.net", false, "", ShardStrategy::CRC, false);

        $builder->setUseHttps(true);
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("https://demos.imgix.net/bridge.png?h=100&w=100", $url);
    }

    public function testExamplePlainSecure() {
        $builder = new UrlBuilder("demos.imgix.net", false, "", ShardStrategy::CRC, false);
        $builder->setSignKey("test1234");
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("http://demos.imgix.net/bridge.png?h=100&w=100&s=bb8f3a2ab832e35997456823272103a4", $url);
    }

    public function testWithFullyQualifiedUrl() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("http://media.giphy.com/media/jCMq0p94fgBIk/giphy.gif");

        $this->assertEquals("https://demos.imgix.net/http%3A%2F%2Fmedia.giphy.com%2Fmedia%2FjCMq0p94fgBIk%2Fgiphy.gif?&s=ffc3359566fe1dc6445ad17d17b98951", $url);
    }

    public function testWithFullyQualifiedUrlWithSpaces() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("https://my-demo-site.com/files/133467012/avatar icon.png");

        $this->assertEquals("https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar+icon.png?&s=f6a4e1504af365564014564f1d7e13de", $url);
    }

    public function testWithFullyQualifiedUrlWithParams() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("https://my-demo-site.com/files/133467012/avatar icon.png?some=chill&params=1");

        $this->assertEquals("https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar+icon.png%3Fsome%3Dchill%26params%3D1?&s=259b9ca6206721752ad7a3ce50f08dd2", $url);
    }

    public function testInclusionOfLibraryVersionParam() {
        $builder = new UrlBuilder("demos.imgix.net", true);
        $url = $builder->createUrl("https://my-demo-site.com/files/133467012/avatar icon.png?some=chill&params=1");
        $composerFileJson = json_decode(file_get_contents("./composer.json"), true);
        $version = $composerFileJson['version'];

        $this->assertEquals("https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar+icon.png%3Fsome%3Dchill%26params%3D1?ixlib=php-" . $version, $url);
    }
  }
?>