<?php
/**----------------------------------------------------------------------------------
* Microsoft Developer & Platform Evangelism
*
* Copyright (c) Microsoft Corporation. All rights reserved.
*
* THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND, 
* EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES 
* OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.
*----------------------------------------------------------------------------------
* The example companies, organizations, products, domain names,
* e-mail addresses, logos, people, places, and events depicted
* herein are fictitious.  No association with any real company,
* organization, product, domain name, email address, logo, person,
* places, or events is intended or should be inferred.
*----------------------------------------------------------------------------------
**/

/** -------------------------------------------------------------
# Azure Storage Blob Sample - Demonstrate how to use the Blob Storage service. 
# Blob storage stores unstructured data such as text, binary data, documents or media files. 
# Blobs can be accessed from anywhere in the world via HTTP or HTTPS. 
#
# Documentation References: 
#  - Associated Article - https://docs.microsoft.com/en-us/azure/storage/blobs/storage-quickstart-blobs-php 
#  - What is a Storage Account - http://azure.microsoft.com/en-us/documentation/articles/storage-whatis-account/ 
#  - Getting Started with Blobs - https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-blobs/
#  - Blob Service Concepts - http://msdn.microsoft.com/en-us/library/dd179376.aspx 
#  - Blob Service REST API - http://msdn.microsoft.com/en-us/library/dd135733.aspx 
#  - Blob Service PHP API - https://github.com/Azure/azure-storage-php
#  - Storage Emulator - http://azure.microsoft.com/en-us/documentation/articles/storage-use-emulator/ 
#
**/

require_once 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\InvalidArgumentTypeException;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;

$resultHtml = "";
$url = "";

if($_FILES){
    $url = upload($_FILES["file"]);
    $resultHtml = "<img style='margin-left: 30px;max-height: 500px;max-width: 500px'src='$url'/>";
    $resultHtml .= "<script>
        processImage('$url');
    </script>";
}

function upload($fileToUpload){
    $connectionString = "DefaultEndpointsProtocol=https;AccountName=nugrahawebapp;AccountKey=Qq9K6BEt4f58LVpUoHgL/j/py469QYTFHFXrvz2IL1AVvFiNREK/6zmDHxdowchP1F3YvQLHuQPNADTYetMvwQ==;EndpointSuffix=core.windows.net";

    $blobClient = BlobRestProxy::createBlobService($connectionString);

    $containerName = "nugraha";

    $filePath = $fileToUpload["tmp_name"];
    $fileName = $fileToUpload["name"];

    try {
        $content = file_get_contents($filePath, "r");

        //Upload blob
        $blobClient->createBlockBlob($containerName, $fileName, $content);

        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix($fileName);

        $url = null;

        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
                $url = $blob->getUrl();
            }

            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());

        // Get blob.
        //$blobClient->getBlob($containerName, $fileToUpload);
        //fpassthru($blob->getContentStream());
        return $url;

    }
    catch(ServiceException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}

function analyze($url) {

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nugraha Image Analyze</title>
</head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<style type="text/css">
    body{
        text-align: center;
        color: #2c3e50;
        font-family: cursive;
    }
</style>
<body background="email-pattern.png">
<script src="https://code.jquery.com/jquery-3.4.1.min.js"> </script>
<script type="text/javascript">
  function processImage(sourceImageUrl) {
    // **********************************************
    // *** Update or verify the following values. ***
    // **********************************************


    // Replace <Subscription Key> with your valid subscription key.
    var subscriptionKey = "3912638b237444369f29a1f997e65ab3";

    // You must use the same Azure region in your REST API method as you used to
    // get your subscription keys. For example, if you got your subscription keys
    // from the West US region, replace "westcentralus" in the URL
    // below with "westus".
    //
    // Free trial subscription keys are generated in the "westus" region.
    // If you use a free trial subscription key, you shouldn't need to change
    // this region.
    var uriBase =
      "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";

    // Request parameters.
    var params = {
      "visualFeatures": "Categories,Description,Color",
      "details": "",
      "language": "en",
    };

    // Make the REST API call.
    $.ajax({
      url: uriBase + "?" + $.param(params),

      // Request headers.
      beforeSend: function(xhrObj){
        xhrObj.setRequestHeader("Content-Type","application/json");
        xhrObj.setRequestHeader(
          "Ocp-Apim-Subscription-Key", subscriptionKey);
      },

      type: "POST",

      // Request body.
      data: '{"url": ' + '"' + sourceImageUrl + '"}',
    })

      .done(function(data) {
        // Show formatted JSON on webpage.
        let description = data.description.captions[0].text;
        document.querySelector("#description").innerText = description;
        $("#responseTextArea").val(JSON.stringify(data, null, 2));
      })

      .fail(function(jqXHR, textStatus, errorThrown) {
        // Display errorThrownr message.
        var errorString = (errorThrown === "") ? "Error. " :
          errorThrown + " (" + jqXHR.status + "): ";
        errorString += (jqXHR.responseText === "") ? "" :
          jQuery.parseJSON(jqXHR.responseText).message;
        alert(errorString);
      });
  }
</script>

<h1><center>Image Analyze</center></h1>
    <p>How to Use ? <br>
        1. <strong>Browse </strong> an Image to be Analyze <br>
        2. Click <strong>Upload & Analyze </strong> to check information about Photos
    </p>

<form id="form" action="index.php" enctype="multipart/form-data" method="post">
    <strong>-></strong><input type="file" name="file" accept="image/*"  class="btn btn-info">
    <strong> -> </strong>
    <button type="submit"  class="btn btn-success">Upload & Analisis</button>
</form><br>

<div id="wrapper" style="width:1420px; display:table;">
    <div style="width: 600px; display: table-cell;">
        Response :
        <textarea id="responseTextArea" class="UIInput" style="width:580px; height:400px;" readonly="readonly"></textarea>
    </div>

    <div style="width:420px; display:table-cell;">
        Source image:
        <?php
            if($resultHtml != ""){
            echo $resultHtml;
        }?>
    </div>

    <div style="width: 400px; display: table-cell;">
        <br><br>
        <h3 style='margin-left: 30px;background: #f39c12;color: white;' id="description"></h3>
    </div>
</div>
</body>
</html>