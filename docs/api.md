# JSON API

The contentpool distribution comes bundled 
with a full [json:api](https://jsonapi.org/) implementation.
The Implementation is done by 
using the [json_api module] of Drupal core. It has thorough
documentation in the [handbook](https://www.drupal.org/docs/8/modules/jsonapi/jsonapi).

## Overview

A typical json:api url looks like this:

    GET|POST     /jsonapi/node/article
    PATCH|DELETE /jsonapi/node/article/{uuid}

The url follows the convention

    /jsonapi/{enitity_type}/{bundle_id}
    /jsonapi/{enitity_type}/{bundle_id}/{uuid}
    
If you query `/jsonapi/node/article` for example you get a 
list of node with of the articles bundle.
You can find in depth info about the api here: 
[json_api module documentation](https://www.drupal.org/docs/8/modules/jsonapi)

The Drupal permission system is respected, so only resources to which 
the requester has access to can be retrieved.
If you need to aquire more permissions than an anonymous user 
you need to authenticate via Oauth2.

## Authentication

Authentication is done via Oauth2. 
You can add new consumers for the api in the contentpool 
backend under `/admin/config/services/consumer`
To create a new consumer click the respective button and fill in a 
label and secret. You have to note down this secret,
because only the hash is saved. You can also select which scopes the 
consumer can access. These scopes are mapped to 
drupal roles, and a user which authenticates with this consumer will 
inherit these roles. In the consumer list you can
see your consumer id.

Once you created your consumer you have all the info to start using 
ouauth2 with a password grant. 
[read more](http://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/)

## Example: Using the json:api in PHP

In this example we will be using the excellent php json:api client 
[yang](https://github.com/woohoolabs/yang)

Instantiate an empty Guzzle request. 
We are using the `php-http/guzzle6-adapter`

    use GuzzleHttp\Psr7\Request;

    $request = new Request("", "");
    
Use the JsonApiRequestBuilder to build a request. 
*   {contentpoolBaseUrl} is the url of the contentpool
*   {uuid} is the uuid of the article
You can add related data to the request via json:api includes. 
To read more about this topic see this section of the json_api 
[docs](https://www.drupal.org/docs/8/modules/jsonapi/includes)


    use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;

    $requestBuilder = new JsonApiRequestBuilder($request);

    $requestBuilder
      ->setProtocolVersion("1.1")
      ->setMethod("GET")
      ->setUri("{contentpoolBaseUrl}/jsonapi/node/article/
    {uuid}?include=field_teaser_media")
      ->setHeader("Accept-Charset", "utf-8");

    $request = $requestBuilder->getRequest();

Send the request

    use Http\Adapter\Guzzle6\Client;
    use WoohooLabs\Yang\JsonApi\Client\JsonApiClient;
    
    $client = Client::createWithConfig([]);
    $jsonApiClient = new JsonApiClient($client);
    $response = $jsonApiClient->sendRequest($request);
   
Query the response. Refer to the [yang](https://github.com/woohoolabs/yang) docs for more info.

    $response->isSuccessful()
    $response->hasDocument()

Optionally Hydrate the response object via the `ClassHydrator` (recommended)

    use WoohooLabs\Yang\JsonApi\Hydrator\ClassHydrator;

    $hydrator = new ClassHydrator();
    $article = $hydrator->hydrate($response->document());

## Example: Getting custom element article teasers for embedding

The contentpool is able to pre-render teasers using custom elements in
order to allow 3rd party sites to easily embed article teasers. The
following example uses the [yang PHP client](https://github.com/woohoolabs/yang)
to fetch the latest 10 article teaser for a given channel:

       use GuzzleHttp\Psr7\Request;
       use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
       
       $request = new Request("", "");
       $requestBuilder = new JsonApiRequestBuilder($request);
       
       $requestBuilder
         ->setProtocolVersion("1.1")
         ->setMethod("GET")
         ->setUri("{contentpoolBaseUrl}/jsonapi/node/article")
         ->setHeader("Accept-Charset", "utf-8");
       
       $requestBuilder
         ->setJsonApiFields(
           [
             "node--article" => ["uuid", "title", "teaser"],
           ]
         )
         ->setJsonApiFilter(
           ['field_channel.id' => '1da04104-2d04-45c8-9c10-c6731672f7ab']
         )
         ->setJsonApiSort(
           ["changed"]
         )
         ->setJsonApiPage(
           ["number" => 1, "size" => 10]
         );
       $request = $requestBuilder->getRequest();

Then send the request as in the example above. 

The scripts that are necessary for rendering the custom elements on the 
client-side should be added to the library `custom_elements/main`. Clients can
access the scripts markup at 
`https://{contentpoolBaseUrl}/contentpool/custom-element-scripts` and easily
append it to embedded HTML markup.
