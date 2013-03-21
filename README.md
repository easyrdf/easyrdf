EasyRdf Lite
=======
EasyRdf Lite is a customized version of [EasyRdf] - a PHP library designed to make it easy to consume and produce [RDF].
Inside of [Wikidata] it is used to generate the RDF.

How to reduce EasyRdf to EasyRdf Lite
------------

* Download EasyRdf from [here]
* Use the lib folder as parent
* Delete the following folders below EasyRdf: Http, Parser, Sparql
* Delete the following files below EasyRdf: GraphStore.php, Http.php, Parser.php, ParsedUri.php
* Adjust EasyRdf.php by deleting all requires of the deleted files and folders

Licensing
---------

The EasyRdf library and tests are licensed under the [BSD-3-Clause] license.
The examples are in the public domain, for more information see [UNLICENSE].


[EasyRdf]:http://www.aelius.com/njh/easyrdf/
[here]:https://github.com/njh/easyrdf/
[Wikidata]:http://framework.zend.com/manual/en/zend.http.client.html
