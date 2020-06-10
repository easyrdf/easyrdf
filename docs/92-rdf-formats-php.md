# RDF/PHP Specification

[Up]

This is a specification for a resource-centric serialisation of RDF in PHP that is compatible with the internal data
structure used by `[ARC]` and is similar in style to the [RDF/JSON Specification]

## Syntax Specification

RDF/PHP represents a set of RDF triples as a series of nested data structures. Each unique subject in the set of
triples is represented as a key in a PHP object (also known as associative array, dictionary or hash table). The value
of each key is a object whose keys are the URIs of the properties associated with each subject. The value of each
property key is an array of objects representing the value of each property.

Blank node subjects are named using the Turtle a string conforming to the `[nodeID production in Turtle]`. For example:

In general, a triple (subject **S**, predicate **P**, object **O**) is encoded in the following structure:

    { "S" : { "P" : [ O ] } }

The object of the triple **O** is a further object with the following keys:

 - **type** one of 'uri', 'literal' or 'bnode' (**required** and must be lowercase)
 - **value** the lexical value of the object (**required**, full URIs should be used, not qnames)
 - **lang** the language of a literal value (**optional** but if supplied it must not be empty)
 - **datatype** the datatype URI of the literal value (**optional**)

The 'lang' and 'datatype' keys should only be used if the value of the 'type' key is "literal".

For example, the following triple:

    <http://example.org/about> <http://purl.org/dc/elements/1.1/title> "Anna's Homepage" .

can be encoded in RDF/PHP as:

    array(
      "http://example.org/about" =>
        array(
           "http://purl.org/dc/elements/1.1/title" =>  array( array( "type" => "literal" , "value" => "Anna's Homepage." ), ),
        )
    );


## Examples

The following RDF/XML:

    <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:foaf="http://xmlns.com/foaf/0.1/"
      xmlns:dc="http://purl.org/dc/elements/1.1/">
      <rdf:Description rdf:about="http://example.org/about">
        <dc:creator>Anna Wilder</dc:creator>
        <dc:title xml:lang="en">Anna's Homepage</dc:title>
        <foaf:maker rdf:nodeID="person" />
      </rdf:Description>
      <rdf:Description rdf:nodeID="person">
        <foaf:homepage rdf:resource="http://example.org/about" />
        <foaf:made rdf:resource="http://example.org/about" />
        <foaf:name>Anna Wilder</foaf:name>
        <foaf:firstName>Anna</foaf:firstName>
        <foaf:surname>Wilder</foaf:surname>
        <foaf:depiction rdf:resource="http://example.org/pic.jpg" />
        <foaf:nick>wildling</foaf:nick>
        <foaf:nick>wilda</foaf:nick>
        <foaf:mbox_sha1sum>69e31bbcf58d432950127593e292a55975bc66fd</foaf:mbox_sha1sum>
      </rdf:Description>
    </rdf:RDF>

Can be represented as the following RDF/PHP structure:

    array(
          "http://example.org/about" => array(
            "http://purl.org/dc/elements/1.1/creator" => array(
                array( "value" => "Anna Wilder", "type" => "literal" ) ) ,
                        "dc:title" => array(
                array( "value" => "Anna's Homepage", "type" => "literal", "lang" => "en" ) ) ,
                        "http://xmlns.com/foaf/0.1/maker" => array(
                array( "value" => "_:person", "type" => "bnode" ) )
          ) ,

          "_:person" => array(
            "http://xmlns.com/foaf/0.1/homepage" => array(
                array( "value" => "http://example.org/about", "type" => "uri" ) ) ,
                        "http://xmlns.com/foaf/0.1/made" => array(
                array( "value" => "http://example.org/about", "type" => "uri" ) ) ,
                        "http://xmlns.com/foaf/0.1/name" => array(
                array( "value" => "Anna Wilder", "type" => "literal" ) ) ,
                        "http://xmlns.com/foaf/0.1/firstName" => array(
                array( "value" => "Anna", "type" => "literal" ) ) ,
                        "http://xmlns.com/foaf/0.1/surname" => array(
                array( "value" => "Wilder", "type" => "literal" ) ) ,
                        "http://xmlns.com/foaf/0.1/depiction" => array(
                array( "value" => "http://example.org/pic.jpg", "type" => "uri" ) ) ,
                        "http://xmlns.com/foaf/0.1/nick" => array(
                        array( "type" => "literal", "value" => "wildling") ,
                         array( "type" => "literal", "value" => "wilda" ) ) ,
                        "http://xmlns.com/foaf/0.1/mbox_sha1sum" => array(
                            array(
                            "value" => "69e31bbcf58d432950127593e292a55975bc66fd",
                            "type" => "literal"
                            )
                        )
          )
    );

[Up]: appendix-a-rdf-formats.md
[RDF/JSON Specification]: appendix-a-rdf-formats-json.md

----

`[ARC]`: Easy RDF and SPARQL for LAMP systems - ARC is a flexible RDF system for semantic web and PHP practitioners.
 It's free, open-source, easy to use, and runs in most web server environments.
 Via: [archive of http://arc.semsol.org/](http://web.archive.org/web/20100807154107/http://arc.semsol.org/)

`[nodeID production in Turtle]`: [Originally](http://web.archive.org/web/20100418050654/http://www.dajobe.org/2004/01/turtle/#nodeID)
 which moved, currently to [W3C TeamSubmission Turtle](http://www.w3.org/TeamSubmission/turtle/#nodeID)
