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


Requirements
------------

* PHP 5.2.0

### Example ###

    $foaf = new EasyRdf_Graph("http://www.aelius.com/njh/foaf.rdf");
    $me = $foaf->primaryTopic();
    echo "My name is: ".$me->get('foaf:name')."\n";


Features
--------

* API documentation written in phpdoc
* Unit tests written using phpunit
* Choice of RDF parser:
  * Built-in: RDF/JSON, N-Triples
  * ARC2: RDF/XML, Turtle, RSS, microformats, eRDF, RDFa, ...
  * Redland Bindings: RDF/XML, N-Triples, Turtle, TriG, RSS Tag Soup, ...
  * rapper: RDF/XML, N-Triples, Turtle, TriG, RSS Tag Soup, ...
* Choice of RDF serialiser:
  * Built-in: RDF/JSON, N-Triples
  * ARC2: RDF/JSON, RDF/XML, N-Triples, Turtle, POSHRDF
  * rapper: RDF/JSON, N-Triples, RDF/XML, Turtle, RSS, Atom, Dot, ...
* Optional support for Zend_Http_Client
* No required external dependancies upon other libraries (PEAR, Zend, etc...)
* Complies with Zend Framework coding style.
* Type mapper - resources of type foaf:Person can be mapped into PHP object of class Foaf_Person
* Comes with a number of examples


More Examples
-------------

* [artistinfo.php](http://github.com/njh/easyrdf/blob/master/examples/artistinfo.php#path) - Example of mapping an RDF type to a PHP Class
* [basic.php](http://github.com/njh/easyrdf/blob/master/examples/basic.php#path) - Basic "Hello World" type example
* [dump.php](http://github.com/njh/easyrdf/blob/master/examples/dump.php#path) - Display the contents of a graph
* [easyspec.php](http://github.com/njh/easyrdf/blob/master/examples/easyspec.php#path) - Format an RDF Vocabulary
* [foafinfo.php](http://github.com/njh/easyrdf/blob/master/examples/foafinfo.php#path) - Display the information in a FOAF file
* [foafmaker.php](http://github.com/njh/easyrdf/blob/master/examples/foafmaker.php#path) - Construct a FOAF document with a choice of serialisations
* [httpget.php](http://github.com/njh/easyrdf/blob/master/examples/httpget.php#path) - No RDF, just test EasyRdf_Http_Client
* [review_extract.php](http://github.com/njh/easyrdf/blob/master/examples/review_extract.php#path) - Extract a review from a page containing Google Review RDFa
* [serialise.php](http://github.com/njh/easyrdf/blob/master/examples/serialise.php#path) - Basic serialisation example
