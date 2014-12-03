<?php
/**
 * Copyright 2014 Aylien, Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class AllTest extends BaseTest
{
  protected static $fixtures = array();

  public static function setUpBeforeClass()
  {
    $dir = __DIR__ . '/../fixtures/';
    $files = new DirectoryIterator($dir);
    foreach ($files as $file) {
      if (preg_match('/(\w+)\.json$/', $file->getFilename(), $matches)) {
        $endpoint = $matches[1];
        $contents = file_get_contents($file->getPathname());
        $json = json_decode($contents, true);
        if ($json) {
          self::$fixtures[$endpoint] = $json['tests'];
        }
      }
    }
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testEmptyKeys()
  {
    $textAPI = new AYLIEN\TextAPI("", "");
  }

  public function testCorrectInputOutput()
  {
    foreach (self::$fixtures as $endpoint => $tests) {
      foreach ($tests as $test) {
        $client = $this->getClient();
        $io_stub = $this->getMockBuilder('AYLIEN\TextAPI\IO_Abstract')->getMock();
        $io_stub->method('execute')->willReturn(json_encode($test['output']));
        $client->setIo($io_stub);
        $r = call_user_func(array($client, $endpoint), $test['input']);
        $this->assertInstanceOf('stdClass', $r);
      }
    }
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testCallingMethodsWithEmptyUrl()
  {
    foreach(self::$fixtures as $endpoint => $tests) {
      $client = $this->getClient();
      call_user_func(array($client, $endpoint), array('url' => ''));
    }
  }


  /**
   * @expectedException \UnexpectedValueException
   */
  public function testHttpErrorExceptionsAreReThrown()
  {
    foreach(self::$fixtures as $endpoint => $tests) {
      $client = $this->getClient();
      $io_stub = $this->getMockBuilder('AYLIEN\TextAPI\IO_Abstract')->getMock();
      $io_stub->method('execute')->will($this->throwException(new \UnexpectedValueException));
      $client->setIo($io_stub);
      call_user_func(array($client, $endpoint), $tests[0]['input']);
    }
  }

  public function testCurlIoAddsAcceptAndContentTypeHeaders()
  {
    $io = new AYLIEN\TextAPI\IO_Curl();
    $this->assertContains('Accept: application/json',
      $io->getHeaders());
    $this->assertContains('Content-Type: application/x-www-form-urlencoded',
      $io->getHeaders());
  }

  public function testIoIsHttpsByDefault()
  {
    $io = new AYLIEN\TextAPI\IO_Curl();
    $this->assertTrue($io->getIsHttps());
  }

  public function testIoUrlBuilder()
  {
    $io = new AYLIEN\TextAPI\IO_Curl();
    $io->setEndpoint('sentiment');
    $this->assertStringStartsWith('https://', $io->getUrl());
    $this->assertStringEndsWith('sentiment', $io->getUrl());
    $io->setIsHttps(false);
    $this->assertStringStartsWith('http://', $io->getUrl());
  }

  public function testGetIoDoesntReturnNull()
  {
    $client = $this->getClient();
    $this->assertInstanceOf('AYLIEN\TextAPI\IO_Abstract', $client->getIo());
  }
}
