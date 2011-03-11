EasyRdf 0.5.2
-------------
* Added a built-in RDF/XML parser
* Made the RDF/XML serialiser use the rdf:type to open tags
* Added support for comments in the N-Triples parser
* Added new EasyRdf_Utils::resolveUriReference() function
* Added the application/rdf+json and text/rdf+n3 mime types


EasyRdf 0.5.1
-------------
* Bug fixes for PHP 5.2


EasyRdf 0.5.0
-------------
* Added support for inverse properties.
* Updated RDF/XML and Turtle serialisers to create new namespaces if possible.
* Added new is_a($type) method to EasyRdf_Resource.
* Added support for passing an array of properties to the get() method.
* Added primaryTopic() method to EasyRdf_Resource.
* EasyRdf_Resource::label() will no longer attempted to shorten the URI,
  if there is no label available.
* Resource types are now stored as resources, instead of shortened URIs.
* Added support for deleting a specific value for property to EasyRdf_Resource.
* Properties and datatypes are now stored as full URIs and not
  converted to qnames during import.
* Change the TypeMapper to store full URIs internally.
* Added bibo and geo to the set of default namespaces.
* Improved bnode links in dump format
* Fix for converting non-string EasyRdf_Literal to string.
* Created an example that resolves UK postcodes using uk-postcodes.com.


EasyRdf 0.4.0
-------------
* Moved source code to Github
* Added an EasyRdf_Literal class
* Added proper support for Datatypes and Languages
* Added built-in RDF/XML serialiser
* Added built-in Turtle serialiser
* Added a new EasyRdf_Format class to deal with mime types etc.
* finished a major refactoring of the Parser/Serialiser registration
* removed all parsing related code from EasyRdf_Graph
* Added a basic serialisation example
* Added additional common namespaces
* Test fixes


EasyRdf 0.3.0
-------------
* Generated Wiki pages from phpdoc
* Filtering of literals by language
* Moved parsers into EasyRdf_Parser_XXX namespace
* Added support for serialisation
* Wrote RDF generation example (foafmaker.php)
* Added built-in ntriples parser/generator
* Added built-in RDF/PHP serialiser
* Added built-in RDF/JSON serialiser
* Added SKOS and RSS to the set of default namespaces.


EasyRdf 0.2.0
-------------
* Added support for Redland PHP bindings
* Added support for n-triples document type.
* Improved blank node handing and added newBNode() method to EasyRdf_Graph.
* Add option to EasyRdf_RapperParser to choose location of rapper command
* Added Rails style HTML tag helpers to examples to make them simpler


EasyRdf 0.1.0
-------------
* First public release
* Support for ARC2 and Rapper
* Built-in HTTP Client
* API Documentation
* PHP Unit tests for every class.
* Several usage examples
