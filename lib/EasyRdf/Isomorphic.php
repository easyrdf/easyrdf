<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2013 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or
 *    promote products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * Functions for comparing two graphs with each other
 *
 * Based on rdf-isomorphic.rb by Ben Lavender:
 * https://github.com/ruby-rdf/rdf-isomorphic
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Isomorphic
{
    /**
     * Check if one graph is isomorphic (equal) to another graph
     *
     * For example:
     *    $graphA = EasyRdf_Graph::newAndLoad('http://example.com/a.ttl');
     *    $graphB = EasyRdf_Graph::newAndLoad('http://example.com/b.ttl');
     *    if (EasyRdf_Isomorphic::isomorphic($graphA, $graphB)) print "Equal!";
     *
     * @param  object EasyRdf_Graph  $graphA  The first graph to be compared
     * @param  object EasyRdf_Graph  $graphB  The second graph to be compared
     * @return boolean True if the two graphs are isomorphic
     */
    public static function isomorphic($graphA, $graphB)
    {
        return is_array(self::bijectionBetween($graphA, $graphB));
    }

    /**
     * Returns an associative array of bnode identifiers representing an isomorphic
     * bijection of one EasyRdf_Graph to another EasyRdf_Graph's blank nodes or
     * null if a bijection cannot be found.
     *
     * @param  object EasyRdf_Graph  $graphA  The first graph to be compared
     * @param  object EasyRdf_Graph  $graphB  The second graph to be compared
     * @return array bnode mapping from $graphA to $graphB
     */
    public static function bijectionBetween($graphA, $graphB, $options = array())
    {
        // Quick initial check: are there differing numbers of subjects?
        if (self::countSubjects($graphA) != self::countSubjects($graphB)) {
            return null;
        }

        // Check if all the statements in Graph A exist in Graph B
        $groundedMatches = self::hasSameStatements($graphA, $graphB);

        if ($groundedMatches) {
            // Check if all the statements in Graph B exist in Graph A
            $groundedMatches = self::hasSameStatements($graphB, $graphA);
        }

        if ($groundedMatches == false) {
            return null;
        } else {
            return array();
        }
    }

    // Return the number of distinct subjects in an EasyRdf_Graph
    private static function countSubjects($graph)
    {
        return count($graph->toRdfPhp());
    }

    // Check if all the statements in $graphA also appear in $graphB
    private static function hasSameStatements($graphA, $graphB)
    {
        $groundedMatches = true;

        foreach ($graphA->toRdfPhp() as $subject => $properties) {
            foreach ($properties as $property => $values) {
                foreach ($values as $value) {
                    if ($groundedMatches && !$graphB->hasProperty($subject, $property, $value)) {
                        $groundedMatches = false;
                    }
                }
            }
        }

        return $groundedMatches;
    }
}
