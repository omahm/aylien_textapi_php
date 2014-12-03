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

namespace AYLIEN\TextAPI;

class IO_Curl extends IO_Abstract
{
  public function execute()
  {
    $ch = curl_init($this->getUrl());
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->getParameters()));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code >= 300) {
      $decoded_error = json_decode($response);
      if ($decoded_error && isset($decoded_error->error)) {
        throw new \UnexpectedValueException($decoded_error->error);
      } else {
        throw new \UnexpectedValueException($response);
      }
    }

    return $response;
  }

  public function getHeaders()
  {
    return array_merge(
      array(
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded'
      ),
      $this->headers
    );
  }
}
