EasyRdf 0.4
-----------
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

EasyRdf 0.3
-----------
* Generated Wiki pages from phpdoc
* Filtering of literals by language
* Moved parsers into EasyRdf_Parser_XXX namespace
* Added support for serialisation
* Wrote RDF generation example (foafmaker.php)
* Added built-in ntriples parser/generator
* Added built-in RDF/PHP serialiser
* Added built-in RDF/JSON serialiser
* Added SKOS and RSS to the set of default namespaces.

EasyRdf 0.2
-----------
* Added support for Redland PHP bindings
* Added support for n-triples document type.
* Improved blank node handing and added newBNode() method to EasyRdf_Graph.
* Add option to EasyRdf_RapperParser to choose location of rapper command
* Added Rails style HTML tag helpers to examples to make them simpler

EasyRdf 0.1
-----------
* First public release
* Support for ARC2 and Rapper
* Built-in HTTP Client
* API Documentation
* PHP Unit tests for every class.
* Several usage examples
