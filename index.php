<?php
require_once "functions.php";
            
            //IF YOU GET A FILES CONTENT SET IT EQUAL TO TEST BELOW
            $test = $test['doc_content'];

            //prepare standardize edi end of line symbols then explode each line
            //we can't standardize symbols so instead we grab the 4th character which should always follow ISA
            //that will be there space symbol and then we replace accordingly
            $test  = str_replace($test[3], "!", $test);
            $test  = str_replace("@", "", $test);
            $test  = str_replace("\\", "", $test);
            $test  = str_replace("~", "", $test);
            $test  = str_replace("^", "", $test);
            $lines = explode(PHP_EOL, $test);
            
            
            
            $transcripts = parserEDI($lines);

            //transcripts is an array of all the values that can be used to display or insert into your own processes. 

?>