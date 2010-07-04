EasyRdf 0.1
-----------
* First public release
* Support for ARC2 and Rapper
* Built-in HTTP Client
* API Documentation
* PHP Unit tests for every class.
* Several usage examples

EasyRdf 0.2
-----------
* Added support for Redland PHP bindings
* Added support for n-triples document type.
* Improved blank node handing and added newBNode() method to EasyRdf_Graph.
* Add option to EasyRdf_RapperParser to choose location of rapper command
* Added Rails style HTML tag helpers to examples to make them simpler

EasyRdf 0.3
-----------
* Generated Wiki pages from phpdoc
* Filtering of literals by language
* Moved parsers into EasyRdf_Parser_XXX namespace
* Added support for serialisation
* Wrote RDF generation example (foafmaker.php)
* Added built-in ntriples parser/generator
* Added built-in RDF/PHP generator
* Added built-in RDF/JSON generator
* Added SKOS and RSS to the set of default namespaces.

Backlog
-------
* Add escaping/unescapting to N-triples parser/generator
* Generate website automatically from the markdown files
* Document the limitations of EasyRdf_Http_Client.
* Add is_a?() method to EasyRdf_Resource
* Add support to EasyRdf_Http_Client for HTTP/1.1 100 Continue
* Finish implementing dump() methods
* Write built-in turtle parser/generator
* Write built-in RDF/XML parser/generator
* Add support for file:// URLs to EasyRdf_Http_Client
* Implement 'turn off magic' static class method to EasyRdf_Resource.
* Add proper support for literals with languages
