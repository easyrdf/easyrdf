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
 * @version    $Id$
 */

/**
 * Sub-class of EasyRdf_Resource that represents an RDF collection (rdf:List)
 *
 * This class can be used to iterate through a collection of items.
 * 
 * Note that items are numbered from 1 (not 0) for consistency with RDF Containers.
 *
 * @package    EasyRdf
 * @link       http://www.w3.org/TR/xmlschema-2/#date
 * @copyright  Copyright (c) 2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Collection extends EasyRdf_Resource implements Iterator
{
    private $position;
    private $current;

    /** Create a new collection - do not use this directly
     *
     * @ignore
     */
    public function __construct($uri, $graph = null)
    {
        $this->position = 1;
        $this->current = null;
        parent::__construct($uri, $graph);
    }

    /** Rewind the iterator back to the start of the collection
     *
     */
    public function rewind()
    {
        $this->position = 1;
        $this->current = null;
    }

    /** Return the current item in the collection
     *
     * @return mixed The current item
     */
    public function current()
    {
        if ($this->position === 1) {
            return $this->get('rdf:first');
        } elseif ($this->current) {
            return $this->current->get('rdf:first');
        }
    }

    /** Return the key / current position in the collection
     *
     * Note: the first item is number 1
     *
     * @return int The current position
     */
    public function key()
    {
        return $this->position;
    }

    /** Move forward to next item in the collection
     *
     */
    public function next()
    {
        if ($this->position === 1) {
            $this->current = $this->get('rdf:rest');
        } elseif ($this->current) {
            $this->current = $this->current->get('rdf:rest');
        }
        $this->position++;
    }

    /** Checks if current position is valid
     *
     * @return bool True if the current position is valid
     */
    public function valid()
    {
        if ($this->position === 1 and $this->hasProperty('rdf:first')) {
            return true;
        } elseif ($this->current !== null and $this->current->hasProperty('rdf:first')) {
            return true;
        } else {
            return false;
        }
    }

    /** Append an item to the end of the collection
     *
     * @param  mixed $value      The value to append
     * @return integer           The number of values appended (1 or 0)
     */
    public function append($value)
    {
        // Find the end of the collection
        $cur = $this;
        $nil = $this->graph->resource('rdf:nil');
        while (($rest = $cur->get('rdf:rest')) and $rest !== $nil) {
            $cur = $rest;
        }

        if ($cur === $this and is_null($rest)) {
            $cur->set('rdf:first', $value);
            $cur->set('rdf:rest', $nil);
        } else {
            $new = $this->graph->newBnode();
            $cur->set('rdf:rest', $new);
            $new->set('rdf:first', $value);
            $new->set('rdf:rest', $nil);
        }

        return 1;
    }
}
