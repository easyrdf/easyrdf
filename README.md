EasyRdf
=======
EasyRdf is a PHP library designed to make it easy to consume and produce RDF. It
was designed for use in mixed teams of experienced and inexperienced RDF
developers. It is written in Object Oriented PHP.

During parsing EasyRdf builds up a graph of PHP objects that can then be walked
around to get the data to be placed on the page.

Data is typically loaded into a EasyRdf_Graph object from source RDF documents.
The source document could either be an RDF file on the web or the output of a
Construct or Describe SPARQL query from a triplestore.

### Example ###

    $foaf = new EasyRdf_Graph("http://www.aelius.com/njh/foaf.rdf");
    $foaf->load();
    $me = $foaf->primaryTopic();
    echo "My name is: ".$me->get('foaf:name')."\n";

Downloads
---------

The latest version of EasyRdf can be downloaded from GitHub.

Links
-----

* [EasyRdf Homepage](http://www.aelius.com/njh/easyrdf/)
* [API documentation](http://www.aelius.com/njh/easyrdf/docs/)
* [Change Log](http://github.com/njh/easyrdf/blob/master/CHANGELOG.md)
* Source Code: <http://github.com/njh/easyrdf>
* Issue Tracker: <http://github.com/njh/easyrdf/issues>

Requirements
------------

* PHP 5.2.1


Features
--------

* API documentation written in phpdoc
* Unit tests written using phpunit
* Choice of RDF parser:
  * Built-in: RDF/JSON, N-Triples, RDF/XML
  * [ARC2](http://arc.semsol.org/): RDF/XML, Turtle, RSS, microformats, eRDF, RDFa, ...
  * [Redland Bindings](http://librdf.org/bindings/): RDF/XML, N-Triples, Turtle, TriG, RSS Tag Soup, ...
  * [rapper](http://librdf.org/raptor/rapper.html): RDF/XML, N-Triples, Turtle, TriG, RSS Tag Soup, ...
* Choice of RDF serialiser:
  * Built-in: RDF/JSON, N-Triples, RDF/XML, Turtle
  * [ARC2](http://arc.semsol.org/): RDF/JSON, RDF/XML, N-Triples, Turtle, POSHRDF
  * [rapper](http://librdf.org/raptor/rapper.html): RDF/JSON, N-Triples, RDF/XML, Turtle, RSS, Atom, Dot, ...
* Optional support for Zend_Http_Client
* No required external dependancies upon other libraries (PEAR, Zend, etc...)
* Complies with Zend Framework coding style.
* Type mapper - resources of type foaf:Person can be mapped into PHP object of class Foaf_Person
* Comes with a number of examples


More Examples
-------------

* [artistinfo.php](http://github.com/njh/easyrdf/blob/master/examples/artistinfo.php#path) - Example of mapping an RDF type to a PHP Class
* [basic.php](http://github.com/njh/easyrdf/blob/master/examples/basic.php#path) - Basic "Hello World" type example
* [converter.php](http://github.com/njh/easyrdf/blob/master/examples/converter.php#path) - Convert RDF from one format to another
* [dump.php](http://github.com/njh/easyrdf/blob/master/examples/dump.php#path) - Display the contents of a graph
* [foafinfo.php](http://github.com/njh/easyrdf/blob/master/examples/foafinfo.php#path) - Display the information in a FOAF file
* [foafmaker.php](http://github.com/njh/easyrdf/blob/master/examples/foafmaker.php#path) - Construct a FOAF document with a choice of serialisations
* [graph_direct.php](http://github.com/njh/easyrdf/blob/master/examples/graph_direct.php#path) - Example of using EasyRdf_Graph directly without EasyRdf_Resource
* [graphstore.php](http://github.com/njh/easyrdf/blob/master/examples/graphstore.php#path) - Store and retrieve data from a SPARQL Graph Store
* [httpget.php](http://github.com/njh/easyrdf/blob/master/examples/httpget.php#path) - No RDF, just test EasyRdf_Http_Client
* [review_extract.php](http://github.com/njh/easyrdf/blob/master/examples/review_extract.php#path) - Extract a review from a page containing Google Review RDFa
* [serialise.php](http://github.com/njh/easyrdf/blob/master/examples/serialise.php#path) - Basic serialisation example
* [sparql_query_form.php](http://github.com/njh/easyrdf/blob/master/examples/sparql_query_form.php#path) - Form to submit SPARQL queries and display the result
* [uk_postcode.php](http://github.com/njh/easyrdf/blob/master/examples/uk_postcode.php#path) - Example of resolving UK postcodes using uk-postcodes.com
* [zend_framework.php](http://github.com/njh/easyrdf/blob/master/examples/zend_framework.php#path) - Example of using Zend_Http_Client and Zend_Loader_Autoloader with EasyRdf
