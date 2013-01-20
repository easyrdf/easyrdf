Property Paths
==============

EasyRdf supports querying the data in a graph using basic property paths.
This is a small subset of the property paths described in [SPARQL 1.1 query language].


You may use the caret character (^) to get an inverse property, for example:

    $person = $homepage->get('^foaf:homepage');

You can use the pipe character (|) to get alternate properties, for example:

    $title = $document->get('dc:title|dc11:title');

You can use a forward slash (/) to follow a property sequence, for example to get
the names of all my friends:

    $names = $me->all('foaf:knows/foaf:name');

Finally, in order to use a full property URI, enclose it in angle brackets:

    $name = $me->get('<http://xmlns.com/foaf/0.1/name>');


[SPARQL 1.1 query language]:http://www.w3.org/TR/sparql11-query/
