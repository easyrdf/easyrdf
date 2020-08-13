EasyRdf 1.0.0
=============

Major new features
------------------

* Minimum version of PHP is now PHP version 7.1, PHP 5.x is no-longer supported
* Usage without composer is not supported anymore
* Library is loaded via PSR-4 autoloader now
* The Redland PHP extension is no-longer supported

Enhancements
------------

* `$graph->isA()` can take full IRIs as second parameter (only qname was accepted earlier, see issue #215)
* `Accept` HTTP-header depends on SPARQL-query type (see issues #231, #226)
* It is possible to set alternate default `Resource` class via `Graph::setDefaultResourceClass()` (see issue #243)
* When calling `Graph::load()` set the HTTP Accept header to the desired format
* The RDF/PHP and RDF/JSON specifications were added to the documentation
* Added text/xml and application/xml MIME types to RDF/XML format
* Added additional namespaces from W3C RDFa context
* Speeding up the turtle parser by optimising the mb_substr calls
* Added support for compressed response body in HTTP client
* Updated to PHP CodeSniffer v3
* Updated to Sami v4 for API documentation
* Updated to PHPUnit v7

API changes
-----------

* Classes are renamed like this: `EasyRdf_Parser_Turtle` → `EasyRdf\Parser\Turtle`. With a single exception: `EasyRdf_Namespace` → `EasyRdf\RdfNamespace` (because `namespace` is a keyword in PHP)
* EasyRdf expects HTTP-client objects compatible with ZendFramework 2.x instead of 1.x now. (zend-http is added to require-dev so tests for it are always run)
* `Resource` implements `ArrayAccess` interface now (see #242)
* Now using PHP Type Hints in some classes, which avoids having to do type checks
* Serialiser class is now abstract
* Implement the ArrayAccess Interface in Resource

Bug Fixes
---------

* Fixes that add compatibility for PHP 7.4
* Unicode-strings are properly encoded in n-triples documents (see #219)
* `RdfPhp` parser validates its input (see #227)
* Timeout is applied to response-times, not only connection-times (see #202)
* `$graph->get()` is reliable after `$graph->delete()` now (see #239, #241)
* Fix for running Graphviz tests against newer versions of Graphviz
* Fixed for when the HTTP server doesn't return Reason-Phrase in the status (see #321)
* Corrections to RDF format URIs
* Fixes for format guessing, so it works with SPARQL-style PREFIX and BASE
* Turtle serialiser improvements and fixes
* Fix for unescaping URIs while parsing ntriples
* Fixed encoding of unicode literals in ntriples

Changes to Examples
-------------------

* Added index.php to examples folder, to make them easier to navigate
* Removed artistinfo.php example (BBC Music no longer publishes RDF)
* Replaced Dbpedialite Villages example with Wikidata Villages example
* Added Open Graph Protocol example
* Changed default URI for converter example
* Fixed namespace for dbpedia categories
* Fixes for UK Postcode example and changed it to use Open Street Map


EasyRdf 0.9.1
=============

Bug Fixes
---------
* Support timeouts for HTTP requests where the server takes a long time to answer. Fixes #202
* Fixed Google Map on UK Postcode example


EasyRdf 0.9.0
=============

Major new features
------------------
* Framing support in `EasyRdf_Serialiser_JsonLd`
* JSON-LD Parser

API changes
-----------
* `EasyRdf_Literal_Decimal` returns strings, instead of floats to avoid losing precision (see issue #178)
* `EasyRdf_Literal_Decimal` requires input-strings which conform to `xs:decimal` format
* `EasyRdf_GraphStore` supports operations over default graph now
* `EasyRdf_Literal` typed as `xs:double` is used for PHP-floats instead of `EasyRdf_Literal_Decimal`
* Exceptions thrown from `EasyRdf_Graph::resource()` use different message-texts now (see issue #159)

Enhancements
------------
* Synced list of default namespaces against [RDFa Core Initial Context](http://www.w3.org/2011/rdfa-context/rdfa-1.1) rev.2014-01-17
* Added support for empty prefixes (see issue #183)
* `EasyRdf_Graph::newAndLoad` throws `EasyRdf_Http_Exception` in case of failure, which gives access to status and response-body. (see issue #149)
* `EasyRdf_Graph` and `EasyRdf_Resource` have 'typesAsResources()' methods now

Bug Fixes
---------
* Fix for Turtle serialisation of FALSE (see issue #179)
* Fix for edge-case in RDF/XML serialisation (see issue #186)
* SPARQL-queries against endpoints which have query-params in their URL (see issue #184)
* Float values are properly handled if locale with "other" separator is active
* Fixed parsing of Turtle-documents with higher utf-8 characters (see issue #195)
* Namespace-prefixes are compliant with RDFXML QName spec (see issue #185)
* `EasyRdf_Namespace` won't generate "short" names with "/" in them anymore (see issue #115)
* `EasyRdf_Parser_RdfXml` respects "base" specified for the document (see issue #157)
* HTML documents are correctly detected now, not as "n-triples" (see issue #206)
* Accept-headers are formatted in locale-independent fashion now (see issue #208)


EasyRdf 0.8.0
=============

Major new features
------------------
* Now PSR-2 compliant
* Added RDFa parser
* Added SPARQL Update support to `EasyRdf_Sparql_Client`

API changes
-----------
* `is_a()` has been renamed to `isA()`
* `isBnode()` has been renamed to `isBNode()`
* `getNodeId()` has been renamed to `getBNodeId()`
* Added a `$value` property to `hasProperty()`
* Renamed `toArray()` to `toRdfPhp()`
* Renamed `count()` to `countValues()` in `EasyRdf_Graph` and `EasyRdf_Resource`
* Made passing a URI to `delete()` behave more like `all()` and `get()` - you must enclose in `<>`
* `dump(true)` has changed to `dump('html')`
* `getUri()` in `EasyRdf_Sparql_Client` has been renamed to `getQueryUri()`

Enhancements
------------
* Added `EasyRdf_Container` class to help iterate through `rdf:Alt`, `rdf:Bag` and `rdf:Seq`
* Added `EasyRdf_Collection` class to help iterate through `rdf:List`
* Added `EasyRdf_Literal_HTML` and `EasyRdf_Literal_XML`
* Changed formatting of `xsd:dateTime` from `DateTime::ISO8601` to `DateTime::ATOM`
* Added `rss:title` to the list of properties that `label()` will check for
* Added support for serialising containers to the RDF/XML serialiser
* Added getGraph method to `EasyRdf_Resource`
* Turtle parser improvements
* Added the `application/n-triples` MIME type for the N-Triples format
* Added support to `EasyRdf_Namespace` for expanding `a` to `rdf:type`
* Added `listNamedGraphs()` function to `EasyRdf_Sparql_Client`
* Added line and column number to exceptions in the built-in parsers

Bug Fixes
---------
* Fixed bug in `EasyRdf_Namespace::expand()` (see issue #114)
* Fix for dumping SPARQL SELECT query with unbound result (see issue #112)
* Sesame compatibility : avoid duplicate Content-Length header
* Fix for for passing objects of type DateTime to $graph->add() (see issue #119)
* Fix for SPARQL queries longer than 2KB (see issue #85)
* Fix for dumping literal with unshortenable datatype uri (see issue #120)
* Fix for getting default mime type or extension when there isn't one
* Fix for missing trailing slash the HTTP client


EasyRdf 0.7.2
=============

Enhancements
------------
* Removed automatic registration of ARC2 and librdf parsers and serialisers
** You must now specifically choose the parser or serialiser
* Refactored `EasyRdf_Literal` with datatypes so that it preserves exact value
* Changed Turtle serialiser to not escape Unicode characters unnecessarily
* Fix for escaping literals objects in Turtle serialiser
* Added a new static function `newAndLoad()` to `EasyRdf_Graph`
* Added setters for each of the components of the URI to the class `EasyRdf_ParsedUri`
* Added option to the converter example, to allow raw output, without any HTML

Bug Fixes
---------
* Fixed broken Redland parser (thanks to Jon Phipps)
* Fix for serialising two bnodes that reference each other in Turtle
* Added support for parsing literals with single quotes in Turtle
* Removed require for EasyRdf/Exception.php
* Fix for serialising `EasyRdf_Literal_DateTime` to Turtle
* Fix for serialising Turtle literals with a shorthand syntax
* Several typo fixes and minor corrections


EasyRdf 0.7.1
=============

Enhancements
------------
* Changed minimum version of PHPUnit to 3.5.15
* Added RDFa namespace
* Added Open Graph Protocol namespace
* Made improvements to formatting of the Turtle serialiser
* Added new splitUri() function to EasyRdf_Namespace
* Made improvements to format guessing

Bug Fixes
---------
* Fix for RDF/XML parser not returning the number of triples
* Added re-mapping of b-nodes to N-Triples and Redland parsers


EasyRdf 0.7.0
=============

API Changes
-----------
* You must now wrap full property URIs in angle brackets

Major new features
------------------
* Added a new pure-PHP Turtle parser
* Added basic property-path support for traversing graphs
* Added support for serialising to the GraphViz dot format (and generating images)
* Added a new class `EasyRdf_ParsedUri` - a RFC3986 compliant URI parser

Enhancements
------------
* The load() function in `EasyRdf_Graph` no-longer takes a $data argument
* The parse() and load() methods, now return the number of triples parsed
* Added count() method to `EasyRdf_Resource` and `EasyRdf_Graph`
* Added localName() method to `EasyRdf_Resource`
* Added htmlLink() method to `EasyRdf_Resource`
* Added methods deleteResource() and deleteLiteral() to `EasyRdf_Graph`
* Added support for guessing the file format based on the file extension
* Performance improvements to built-in serialisers

Environment changes
-------------------
* Added PHP Composer description to the project
* Now properly PSR-0 autoloader compatible
* New minimum version of PHP is 5.2.8
* Changed test suite to require PHPUnit 3.6
* Changed from Phing to GNU Make based build system
* Added automated testing of the examples

Bug Fixes
---------
* Fix for loading https:// URLs
* Fix for storing the value 0 in a `EasyRdf_Graph`
* Fix for HTTP servers that return relative URIs in the Location header
* Fix for Literals with languages in the SPARQL Query Results XML Format
* Fix for SPARQL servers that put extra whitespace into the XML result
* Fix for the httpget.php example in PHP 5.4+


EasyRdf 0.6.3
=============
* Added $graph->parseFile() method.
* Added support for SSL (https) to the built-in HTTP client
* Fixes for HTTP responses with a charset parameter in the Content Type.
* Improved error handling and empty documents in JSON and rapper parsers.
* Added connivence class for xsd:hexBinary literals:
  - `EasyRdf_Literal_HexBinary`
* Made EasyRdf more tolerant of 'badly serialised bnodes'
* Fix for SPARQL servers that return charset in the MIME Type.
* Fix for using xml:lang in SPARQL 1.1 Query Results JSON Format
* Changed datetime ISO formatting to use 'Z' instead of +0000 for UTC dateTimes
* Added the namespace for 'The Cert Ontology' to EasyRdf.


EasyRdf 0.6.2
=============
* Bug fix for missing triples in the RDF/XML serialiser.
* Added countTriples() method to `EasyRdf_Graph`.
* Re-factored the mechanism for mapping RDF datatypes to PHP classes.
* Added subclasses of `EasyRdf_Literal` for various XSD datatypes:
  - `EasyRdf_Literal_Boolean`
  - `EasyRdf_Literal_Date`
  - `EasyRdf_Literal_DateTime`
  - `EasyRdf_Literal_Decimal`
  - `EasyRdf_Literal_Integer`
* Made the Redland based parser write triples directly to `EasyRdf_Graph`
* Added support for datatypes and languages in the `EasyRdf_Parser_Ntriples` parser.
* Fix for parsing XML Literals in RDF/XML


EasyRdf 0.6.1
=============
* Updated API documentation for new classes and methods added in 0.6.0
* Added a description to the top of the source code for each example.
* Changed the generated bnode identifier names from eidXXX to genidXXX.
* Implemented inlining of resources in the RDF/XML serialiser.
* Added new reversePropertyUris() method to `EasyRdf_Graph` and `EasyRdf_Resource`.
* Added addType() and setType() to `EasyRdf_Resource`.
* Added a textarea to the converter example.
* Added support for parsing the json-triples format.
* Renamed `EasyRdf_SparqlClient` to `EasyRdf_Sparql_Client`
* Renamed `EasyRdf_SparqlResult` to `EasyRdf_Sparql_Result`
* Fix for $graph->isEmpty() failing after adding and deleting some triples
* Added new `EasyRdf_DatatypeMapper` class that allows you to map RDF datatypes to PHP classes.
* Renamed guessDatatype() to getDatatypeForValue() in `EasyRdf_Literal`.
* Added getResource() and allResources() to `EasyRdf_Graph` and `EasyRdf_Resource`
* Implemented value casting in literals based on the datatype.


EasyRdf 0.6.0
=============
* Major re-factor of the way data is stored internally in `EasyRdf_Graph`.
* Parsing and serialising is now much faster and will enable further optimisations.
* API is mostly backwards-compatible apart from:
  - Changed inverse property operator from - to ^ to match Sparql 1.1 property paths.
  - New `EasyRdf_Graphs` will not automatically be loaded on creation
    You must now call $graph->load();
  - Setting the default HTTP client is now part of a new `EasyRdf_Http` class
  - It is no-longer possible to add multiple properties at once using an associative array.
* Added methods to `EasyRdf_Graph` for direct manipulation of triples.
* Added new `EasyRdf_GraphStore` - class for fetching, saving and deleting graphs to a Graph Store over HTTP.
* Added new `EasyRdf_SparqlClient` and `EasyRdf_SparqlResult` - class for querying a SPARQL endpoint over HTTP.
* Added q values for each Mime-Type associated with an `EasyRdf_Format`.
* New example demonstrating integration with the Zend Framework.
* New `EasyRdf_HTTP_MockClient` class makes testing easier.


EasyRdf 0.5.2
=============
* Added a built-in RDF/XML parser
* Made the RDF/XML serialiser use the rdf:type to open tags
* Added support for comments in the N-Triples parser
* Added new resolveUriReference() function to `EasyRdf_Utils`
* Added the application/rdf+json and text/rdf+n3 mime types


EasyRdf 0.5.1
=============
* Bug fixes for PHP 5.2


EasyRdf 0.5.0
=============
* Added support for inverse properties.
* Updated RDF/XML and Turtle serialisers to create new namespaces if possible.
* Added new is_a($type) method to `EasyRdf_Resource`.
* Added support for passing an array of properties to the get() method.
* Added primaryTopic() method to `EasyRdf_Resource`.
* The function label() in `EasyRdf_Resource` will no longer attempted to shorten the URI,
  if there is no label available.
* Resource types are now stored as resources, instead of shortened URIs.
* Added support for deleting a specific value for property to `EasyRdf_Resource`.
* Properties and datatypes are now stored as full URIs and not
  converted to qnames during import.
* Change the TypeMapper to store full URIs internally.
* Added bibo and geo to the set of default namespaces.
* Improved bnode links in dump format
* Fix for converting non-string `EasyRdf_Literal` to string.
* Created an example that resolves UK postcodes using uk-postcodes.com.


EasyRdf 0.4.0
=============
* Moved source code to Github
* Added an `EasyRdf_Literal` class
* Added proper support for Datatypes and Languages
* Added built-in RDF/XML serialiser
* Added built-in Turtle serialiser
* Added a new `EasyRdf_Format` class to deal with mime types etc.
* finished a major refactoring of the Parser/Serialiser registration
* removed all parsing related code from `EasyRdf_Graph`
* Added a basic serialisation example
* Added additional common namespaces
* Test fixes


EasyRdf 0.3.0
=============
* Generated Wiki pages from phpdoc
* Filtering of literals by language
* Moved parsers into `EasyRdf_Parser_XXX` namespace
* Added support for serialisation
* Wrote RDF generation example (foafmaker.php)
* Added built-in ntriples parser/generator
* Added built-in RDF/PHP serialiser
* Added built-in RDF/JSON serialiser
* Added SKOS and RSS to the set of default namespaces.


EasyRdf 0.2.0
=============
* Added support for Redland PHP bindings
* Added support for n-triples document type.
* Improved blank node handing and added newBNode() method to `EasyRdf_Graph`.
* Add option to `EasyRdf_RapperParser` to choose location of rapper command
* Added Rails style HTML tag helpers to examples to make them simpler


EasyRdf 0.1.0
=============
* First public release
* Support for ARC2 and Rapper
* Built-in HTTP Client
* API Documentation
* PHP Unit tests for every class.
* Several usage examples
