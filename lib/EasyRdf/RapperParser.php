<?php


/**
 * An RDF parsing class that uses the 'rapper' command line tool.
 */
class EasyRdf_RapperParser
{
    /**
     * Parse an RDF document
     * @paramÊstringÊ$dataÊthe document data.
     * @param string $input_type the format of the input document.
     * @return string the converted document, or null if the convertion failed.
     */
    public function parse($uri, $data, $docType)
    {
        # Don't even attempt to parse it if it is empty
        if (trim($data) == '') return array();

        // Open a pipe to the rapper command
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("file", "php://stderr", "w")
        );

        $process = proc_open(
            "rapper --quiet -i $docType -o json -e - $uri",
            $descriptorspec, $pipes, '/tmp', null
        );
        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
      
            fwrite($pipes[0], $data);
            fclose($pipes[0]);
      
            $data = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
      
            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $returnValue = proc_close($process);
            if ($returnValue) {
                # FIXME: throw exception or log error?
                echo "rapper returned $returnValue\n";
                return null;
            }
        } else {
            // FIXME: throw error?
        }

        // Parse in the JSON
        return json_decode($data, true);
    }
}
