<?php
function parserEDI($lines){
    //set values 
    $semesterCount = 0; 
    $transferCount=0; 
    $degreeCount = 0;
    $collegeCount = 0; 
    $college[$collegeCount] = array(); 
    $loop ="";
    $prev =""; 
    $transcript=array(); 
    $notes= array();

    $ierfArray = array() ;

    $transcripts = array();
    $transfer=False;

    foreach($lines as $line) {
        $items = explode("!",$line);
        

        
        switch ($items[0]){
            case "ISA":
                //set values at the beggining of each document
                $isaItems = isa($items); 
                
                
                $semesterCount = 0; 
                $personal=array();
                $names=array();
                $degree=array();
                $colleges = array();
                $pcl  = array(); 
                $transferItems = array(); 
                $exams = array(); 


                break;

            case "GE":
                $geItems = ge($items); 
                

                break;    
            case "IEA":
            //beginning of each transcript 
            //if $transfer is true then its the second transcript of the file so mark it as false 
            //and unset the value to be reset by the new transcript
                
                

                break;    
            case "GS":
                $gsItems = gs($items); 
                
                break;    
            case "IN1":
                $in1Items = in1($items); 
                $loop ="in1";
                array_push($personal, $in1Items); 
                break;    
            case "IN2":
                $in2Items = in2($items); 
                if($in1Items['guardianType']=="Student"||$in1Items['guardianType']==""){
                 $personal["name"][str_replace(" ","_",$in2Items["componentName"])]=$in2Items["name"];
                }
                
                break;    
            case "ST":
                $stItems = st($items); 
                $prev ="st";
                break;
            case "BGN":
                $bgnItems = bgn($items); 
                $prev ="bgn";
                $college =NULL;
                $collegeCount = 0; 
                $loop="";
                break;
            case "ERP":
                $erpItems = erp($items); 
                $prev ="erp";
                break;
            case "REF":
                
                if($loop=="n3"){
                    $college[$collegeCount]["contact"]["reference"] = array(ref($items));
                }
                else if($loop=="crs"){
                    $college[$collegeCount]["semester"][$semesterCount]["courses"][$courseNumber-1]["reference"]= ref($items);
                }
                else{
                    $prev ="ref";
                    $refItems = ref($items); 
                    $personal["id"][str_replace(" ","_",$refItems['referenceQualifier'])] = $refItems['referenceID'];
                }
                break;
            case "DMG":
                $dmgItems = dmg($items); 
                $prev ="dmg";
                $personal["otherInfo"]= $dmgItems;
                break;
            case "LUI":
                $prev ="lui";
                $luiItems = lui($items); 
                if($loop=="crs"){
                    $college[$collegeCount]["semester"][$semesterCount]["courses"][$courseNumber-1]["language"]= $luiItems;
                }
                else if ($loop=="mks"){
                    $college[$collegeCount]["marksAwarded"]["language"]=  $mksItems;
                }
                break;
            case "IND":
                $prev ="ind";
                $indItems = ind($items); 
                break;
            case "DTP":
                if($loop=="atv"){
                    $college[$collegeCount]["awards"]["date"] = array( dtp($items));
                }
                else{
                    $prev ="dtp";
                    $dtpItems = dtp($items); 
                    array_push($college[$collegeCount]['dtp'], $dtpItems) ;
                }
                break;
            case "RAP":
                $prev ="rap";
                $rapItems = rap($items); 
                if($loop=="crs"){
                    $college[$collegeCount]["semester"][$semesterCount]["courses"][$courseNumber-1]["requirement"]= $rapItems;
                }
                break;
            case "PCL":
                $prev ="pcl";
                array_push($pcl, pcl($items)); 
                break;
            case "N1":
                $n1Items = n1($items); 
                if($loop==""||$loop=="n1"){
                    if($n1Items["identifierCode"]=="Postsecondary Education Sender"||$n1Items["identifierCode"]=="Pre-kindergarten to Grade 12 Sender"||$n1Items["identifierCode"]=="Pre-Kindergarten to Grade 12 Sender"){
                        if($college!=NULL){
                            $collegeCount++; 
                             array_push($colleges, $college); 
                            $college[$collegeCount] = $n1Items;
                            $college[$collegeCount]["degrees"] = NULL;
                            $college[$collegeCount]["courses"] = array();
                            
                        }
                        else{
                            $college = array($n1Items);
                            $college[$collegeCount]["courses"] = array();
                        }
                    }

                    $loop="n1";
                }
                else if($loop=="sst"){
                    $college[$collegeCount]["status"]['name'] = array( n1($items));
                }
                else if($loop=="ses"){
                    $college[$collegeCount]["semester"][$semesterCount]["name"] = n1($items); 
                    $transerName = n1($items);
                    if($transerName['institutionName']==""||$transerName['institutionName']==NULL){
                        foreach ($pcl as $pclCollege) {
                            if($pclCollege["institutionCode"]==$transerName['institutionCode']){
                                $transerName['institutionName'] = $pclCollege["institutionName"];                                                     
                            }
                        }
                    }
                    //N1 session
                }
                else if($loop=="crs"){
                    $college[$collegeCount]["semester"][$semesterCount]["courses"][$courseNumber-1]["name"]=  n1($items);
                    $transferItems[$transferCount]['transferCollege'] = n1($items);
                }
                else if($loop=="deg"){
                    $college[$collegeCount]["degrees"][$degreeCount]["name"]= n1($items);
                }
                
                
            
                        
                break;
            case "N2":
                $n2Items = n2($items); 
                if($loop=="n1"){
                    $college[$collegeCount]["additionalInfo"] = array(n2($items));
                }
                break;
            case "N3":
                if($loop=="n1"){
                    $college[$collegeCount]["addressInfo"] = array(n3($items)); 
                }
                else if($loop=="in1"){
                    $college[$collegeCount]["contact"]["address"] = array(n3($items)); 
                    $loop="n3";
                }
                else if($loop=="sst"){
                    $college[$collegeCount]["status"]['addressInfo'] = array( n3($items));
                }
                else if($loop=="ses"){
                    $college[$collegeCount]["semester"][$semesterCount]["addressInfo"] = n3($items);  
                }
                
                    
                break;
            case "N4":
            if($loop=="n1"){
                    $college[$collegeCount]["geographicLocation"] = array(n4($items)); 
                }
            else if($loop=="n3"){
                $college[$collegeCount]["contact"]["geographic"] = array(n4($items));
            }
            else if($loop=="sst"){
                $college[$collegeCount]["status"]['geographicLocation'] = array( n4($items));
            }
            else if($loop=="ses"){
                $college[$collegeCount]["semester"][$semesterCount]["geographicLocation"] = n4($items); 
                $college[$collegeCount]["semester"][$semesterCount]["courses"] = array();  
            }
            else if($loop=="crs"){
                $college[$collegeCount]["semester"][$semesterCount]["courses"][$courseNumber-1]["name"]=   n4($items);
            }
                
                $n4Items = n4($items); 
                break;
            case "PER":
            if($loop=="n1"){
                    $college[$collegeCount]["person"] = array(per($items));
                }
            else if($loop=="n3"){
                $college[$collegeCount]["contact"]["person"] = array(per($items));
            }
                $perItems = per($items); 
                break;
            case "SST":
                $sstItems = sst($items); 
                $college[$collegeCount]["status"] = array( sst($items)); 
                $loop="sst";
                break;
            case "SSE":
                if($loop=="sst"){

                }
                else if($loop=="ses"){
                    $college[$collegeCount]["semester"][$semesterCount]["entry_exit_info"] = sse($items); 
                }
                else{
                    $sseItems = sse($items); 
                    array_push($college[$collegeCount]['sse'], $sseItems) ;
                }
                
                break;
            case "ATV":
                $college[$collegeCount]["awards"] = array( atv($items));
                $loop="atv";
                
                
                break;
            case "TST":
                $college[$collegeCount]["test"] = array(tst($items)); 
                break;
            case "SUM":
                if($loop=="sbt"){
                    $loop="sum1";
                    $college[$collegeCount]["collegeSummary"] =sum($items); 
                }
                else if($loop=="ses"){
                    $loop="sum2";
                    $college[$collegeCount]["semester"][$semesterCount]["semesterSummary"] =sum($items); 
                    
                }
                else if($loop=="deg"){
                    $college[$collegeCount]["degrees"][$degreeCount]["degreeSummary"]=sum($items);
                }
                else if($loop=="sst"){
                    
                    $loop="sum3";
                    $college[$collegeCount]["collegeSummary"] =sum($items);
                    
                    if($college[$collegeCount]["collegeSummary"]['gpa']=="" && $college[$collegeCount]["collegeSummary"]["pointsForGPA"]!=""){
                        $college[$collegeCount]["collegeSummary"]['gpa'] = round($college[$collegeCount]["collegeSummary"]["pointsForGPA"]/$college[$collegeCount]["collegeSummary"]["creditHoursGPA"], 2);
                    }

                }
                
                
                break;
            case "LX":
                $personal["medical"] = array();
                $personal["medical"]["assignedNumber"] = array(lx($items)); 
                
                break;
            case "IMM":
                    $personal["medical"]["imunizationStatus"]=array(imm($items));
                break;
            case "SES":
                $loop="ses";
                if($transfer==TRUE){

                    


                    if(count($college[$collegeCount]["semester"][$semesterCount]["courses"])==0){
                            unset($college[$collegeCount]["semester"][$semesterCount]);
                            $semesterCount=--$semesterCount;
                        }    
                }
                $transfer=False;
                $semesterCount ++;
                
                
                $courseNumber=0 ; 
                $transerName = NULL; 
                $college[$collegeCount]["semester"][$semesterCount] = ses($items); 
                $college[$collegeCount]["semester"][$semesterCount]["courses"]=array();
                $college[$collegeCount]["semester"][$semesterCount]["semesterSummary"]=null;
                $college[$collegeCount]["semester"][$semesterCount]["note"]=null;
                break;
            case "CRS":
                $loop="crs";
                $transfer=False;
                $crsItems = crs($items); 
                if($crsItems["overallSourceCode"]=="Transfer Credit"){
                    $transfer=True; 

                    $transferItems[$transferCount]=$crsItems ;

                    if($transerName!=NULL){
                        $transferItems[$transferCount]['transferCollege']=$transerName ; 
                    }

                    
                    
                    $transferCount++;
                    
                    
                }
                elseif($crsItems["type"]=="Exam Credit"){
                    $transfer=True; 

                    array_push($exams, $crsItems);
                                                 
                }
                else{
                    $courseNumber=++$courseNumber;
                    array_push($college[$collegeCount]["semester"][$semesterCount]["courses"], $crsItems);

                    $transfer=False;
                }
                

                
                break;
            case "CSU":
                $csuItems = csu($items); 
                if($loop=="crs"&&$transfer==False){
                    $college[$collegeCount]["semester"][$semesterCount]["courses"][$courseNumber]["courseData"]=$csuItems;
                }

                break;
            case "NTE":
                if($loop=="n1"){
                    $college[$collegeCount]["contact"]["special_note"] = array(nte($items));
                }
                else if($loop=="sbt"){
                    $college[$collegeCount]["subTest"]["notes"]= nte($items); 
                }
                else if($loop=="sum1"){
                    if(is_array($notes)==FALSE){
                        $notes= array(); 
                    }
                    array_push($notes, nte($items)); 
                }
                else if($loop=="sum2"){
                    if(is_array($college[$collegeCount]["semester"][$semesterCount]["note"])==FALSE){
                     $college[$collegeCount]["semester"][$semesterCount]["note"]= array(); 
                    }
                    array_push($college[$collegeCount]["semester"][$semesterCount]["note"], nte($items)); 
                }
                else if($loop=="ses"){
                    array_push($college[$collegeCount]["semester"][$semesterCount]["note"], nte($items));  
                }
                else if($loop=="crs"){
                    $college[$collegeCount]["semester"][$semesterCount]["courses"][$courseNumber-1]["note"]=  nte($items);
                }
                else if($loop=="deg"){
                	if(is_array($college[$collegeCount]["degrees"][$degreeCount]["note"])==FALSE){
                     $college[$collegeCount]["degrees"][$degreeCount]["note"]= array(); 
                    }
                	array_push( $college[$collegeCount]["degrees"][$degreeCount]["note"], nte($items));  
                }
                else{
                     
                    
                    if($isaItems['id']=='IERF00         '){
                        
                            $ierf = explode(":",$items[2] );
                        if (preg_match("/w*2/",$ierf[0])) {
                            $ierf[0] = str_replace('_2', '', $ierf[0]);
                            $ierfArray[1][$ierf[0]]= $ierf;
                        }
                        elseif(preg_match("/w*3/",$ierf[0])){
                            $ierf[0] = str_replace('_3', '', $ierf[0]);
                            $ierfArray[2][$ierf[0]]= $ierf;
                        }
                        elseif(preg_match("/w*4/",$ierf[0])){
                            $ierf[0] = str_replace('_4', '', $ierf[0]);
                            $ierfArray[3][$ierf[0]]= $ierf;
                        }    
                        elseif(preg_match("/w*5/",$ierf[0])){
                            $ierf[0] = str_replace('_5', '', $ierf[0]);
                            $ierfArray[4][$ierf[0]]= $ierf;
                        }
                        else{
                            $ierfArray[0][$ierf[0]]= $ierf;
                        }        
                        
                    }
                    else{
                        $nteItems = nte($items);
                        array_push($notes, $nteItems);
                    }
                }

                $prev ="nte";
                break;
            case "MKS":
                $loop = "mks";
                $mksItems = mks($items);
                $college[$collegeCount]["marksAwarded"]=  $mksItems; 

                break;
            case "DEG":
                $degreeCount=++$degreeCount; 
                $loop="deg";
                $degItems = deg($items); 

                


                $college[$collegeCount]["degrees"][$degreeCount]= $degItems ;
                $college[$collegeCount]["degrees"][$degreeCount]["note"]=null;
                
                break;
            case "FOS":
                $fosItems = fos($items); 
                $college[$collegeCount]["degrees"][$degreeCount]["fos"]= fos($items);

                break;
            case "SE":
                $seItems = se($items); 

                if($transfer==TRUE){
                    if(count($college[$collegeCount]["semester"][$semesterCount]["courses"])==0){
                            unset($college[$collegeCount]["semester"][$semesterCount]);
                            
                        }    
                        $transfer=False;
                }

                //if there semester isn't null print out the semester content
                if(isset($college[$collegeCount]["semester"])){
                    foreach($college[$collegeCount]["semester"] as &$semesterCount){

                        if($semesterCount["semesterSummary"]==NUll){
                            $info = calcGPA($semesterCount);
                            $semesterCount['semesterSummary']['creditType']="";
                            $semesterCount['semesterSummary']['courseLevel']="";
                            $semesterCount['semesterSummary']['creditHoursAttempted']=$info['attempted'];
                            $semesterCount['semesterSummary']['creditHoursEarned']=$info['credits'];
                            $semesterCount['semesterSummary']['gpa']= $info['gpa'];
                           
                        }
                        unset($semesterCount);
                        //unset semester count to be restarted with new transcript
                    }
                }

                

                $semesterCount = 0; 
                array_push($colleges, $college);
                //verify everyting is set otherwise set it to null
                //Make sure all the different types of names are set
                isset($personal["name"]["Last_Name"]) ? $personal["name"]["Last_Name"] : Null;
                isset($personal["name"]["First_Name"]) ? $personal["name"]["First_Name"] : Null;
                isset($personal["name"]["First_Middle_Name"]) ? $personal["name"]["First_Middle_Name"] : Null;
                isset($personal["name"]["Second_Middle_Name"]) ? $personal["name"]["Second_Middle_Name"] : Null;
                isset($personal["name"]["Prefix"]) ? $personal["name"]["Prefix"] : "";
                isset($personal["name"]["First_Initital"]) ? $personal["name"]["First_Initital"] : Null;
                isset($personal["name"]["First_Middle_Initital"]) ? $personal["name"]["First_Middle_Initital"] : Null;
                isset($personal["name"]["Second_Middle_Initital"]) ? $personal["name"]["Second_Middle_Initital"] : Null;
                isset($personal["name"]["Suffix"]) ? $personal["name"]["Suffix"] : Null;
                isset($personal["name"]["Combined_(Unstructured)_Name"]) ? $personal["name"]["Combined_(Unstructured)_Name"] : Null;
                isset($personal["name"]["Composite_Name"]) ? $personal["name"]["Composite_Name"] : Null;
                isset($personal["name"]["Middle_Names"]) ? $personal["name"]["Middle_Names"] : Null;
                //make sure other info is set
                isset($personal['otherInfo']['dob'])?$personal['otherInfo']['dob']:Null;
                isset($personal['otherInfo']['gender'])?$personal['otherInfo']['gender']:Null;
                isset($personal['id']['SSN'])?$personal['id']['SSN']:Null;
                isset($colleges['semester'])?$colleges['semester']:Null;
                isset($colleges['collegeSummary'])?$colleges['collegeSummary']:Null;

                if($isaItems['id']=='IERF00         '){



                    $transcript =array("Personal"=>$personal, "Colleges"=>$colleges, "PCL"=>$pcl, "Notes"=>$notes , "Transfer"=>$transferItems,  "IERF"=>$ierfArray, "IERFflag"=>1);


                        
                }
                else{
                    $transcript =array("Personal"=>$personal, "Colleges"=>$colleges, "PCL"=>$pcl, "Notes"=>$notes , "Transfer"=>$transferItems, "Exams"=>$exams, "IERFflag"=>0);
                    
                    

                }
                array_push($transcripts,$transcript);
                //
                
                //Try and find person id of student through first name, last name, and DOB

                
                $colleges= array();
                break;
            case "SBT":
                $college[$collegeCount]["subTest"]=sbt($items);
                $loop="sbt";
                
                break;
            case "SRE":
                $college[$collegeCount]["subTest"]["testScores"]=sre($items);
                break;
        }


    }
    
    return $transcripts; 
}

function crs($items){

    $crs01= array(
            "A"=>"AP", 
            "B"=>"Exam Credit", 
            "C"=>"CLEP", 
            "D"=>"DANTES", 
            "E"=>"Life Experience", 
            "F"=>"Study Abroad", 
            "G"=>"CEEB", 
            "H"=>"Incomplete Grade",
            "I"=>"IB", 
            "M"=>"Military", 
            "N"=>"Correspondence",
            "P"=>"ACE/PONSI",
            "R"=>"Regular",
            "T"=>"Transfer",
            "V"=>"AUDIT/VS",
            "W"=>"Work Experience",
            "X"=>"N/A",
            "Z"=>"Other"
        ); 
    $crs02= array(
            "A"=>"Adult Credit", 
            "C"=>"Continuing Education", 
            "G"=>"Carnegie Units", 
            "N"=>"NC", 
            "Q"=>"Quarter", 
            "S"=>"Semester", 
            "U"=>"Units", 
            "V"=>"Vocational",
            "X"=>"Other",
        );
    $crs08= array(
        "1"=>"Remedial", 
        "2"=>"Basic", 
        "3"=>"TA", 
        "4"=>"General", 
        "5"=>"Applied", 
        "6"=>"Survey", 
        "7"=>"Regular", 
        "8"=>"Spec. Top.",
        "9"=>"Adv.",
        "D"=>"Dual Level",
        "G"=>"Grad",
        "H"=>"Higher",
        "I"=>"Institutional Credit",
        "L"=>"Lower",
        "M"=>"Work in the Major",
        "P"=>"Professional",
        "R"=>"Remedial",
        "U"=>"Undergraduate",
        "10"=>"Honors",
        "11"=>"Gifted",
        "12"=>"AP",
        "13"=>"Special Ed.",
        "14"=>"Vocational",
        "15"=>"Ind. Study",
        "16"=>"Work Experience",
        "17"=>"Adult Basic",
        "18"=>"Adult Secondary",
        "19"=>"IB",
        "AR"=>"Academic Renewal",
        "DL"=>"Dual Level",
    );   
    $crs09= array(
            "N"=>"Repeated Not Counted", 
            "R"=>"Repeated Counted", 
            "X"=>"Other", 
        ); 
    $crs10= array(
            "75"=>"State Assigned Number", 
            "76"=>"Local School Number", 
            "81"=>"CIP", 
            "82"=>"HEGIS", 
            "CA"=>"Canada Course Codes", 
            "CC"=>"Canada Curriculum Codes", 
        );
    $crs13= array(
        "01"=>"1", 
        "02"=>"2", 
        "03"=>"3", 
        "04"=>"4", 
        "05"=>"5", 
        "06"=>"6", 
        "07"=>"7", 
        "08"=>"8",
        "09"=>"9",
        "0K"=>"Kindergarten",
        "10"=>"10",
        "11"=>"11",
        "12"=>"12",
        "21"=>"PostSecondary First Year",
        "22"=>"PostSecondary Sophmore",
        "23"=>"PostSecondary Junior",
        "24"=>"PostSecondary Senior",
        "25"=>"Post-Bacc",
        "26"=>"Non-Degree Graduate",
        "27"=>"Professional",
        "28"=>"Masters",
        "29"=>"Doctoral",
        "30"=>"Postdoctoral",
        "31"=>"Bachelor Preliminary",
        "32"=>"Fifth year",
        "33"=>"Masters Qualifying Year",
        "AD"=>"Adult",
        "EL"=>"Elementary",
        "IF"=>"Infant",
        "MS"=>"Middle/Junior School",
        "P0"=>"Pre-Kindergarten L0",
        "P1"=>"Pre-Kindergarten L1",
        "P2"=>"Pre-Kindergarten L2",
        "P3"=>"Pre-Kindergarten L3",
        "P4"=>"Pre-Kindergarten L4",
        "P5"=>"Pre-Kindergarten L5",
        "PF"=>"Professional",
        "PK"=>"Pre-Kindergarten",
        "SS"=>"Secondary School",
        "UN"=>"Ungraded",
        "VS"=>"Vocational School",
    );  
    $crs20= array(
            "IA"=>"Institutional Agreement", 
            "MC"=>"Multiple Campus Course Offering", 
            "TC"=>"Transfer Credit"
        ); 
$itemSize = count($items); 
    for($i=$itemSize; $i<=20;$i++){
        $items[$i]="";
    }
    $type = isset($crs01[$items[1]]) ? $crs01[$items[1]] : $items[1]; 
    $semType = isset($crs02[$items[2]]) ? $crs02[$items[2]] : $items[2];
    $courseLevel = isset($crs08[$items[8]]) ? $crs08[$items[8]] : $items[8];
    $repeated = isset($crs09[$items[9]]) ? $crs09[$items[9]] : $items[9];
    $codeQualifier = isset($crs10[$items[10]]) ? $crs10[$items[10]] : $items[10];
    $gradeLevel = isset($crs13[$items[13]]) ? $crs13[$items[13]] : $items[13];
    $overallSourceCode = isset($crs20[$items[20]]) ? $crs20[$items[20]] : $items[20];
    
    $courses = array("type"=>$type, "semesterType"=>$semType, "courseHours"=>$items[3],"academicHours"=>$items[4],"gradeQualifier"=>$items[5],"academicGrade"=>$items[6], "honors"=>$items[7],"courseLevel"=>$courseLevel, "repeatedCode"=>$items[9],  "repeated"=>$repeated, "codeQualifier"=>$codeQualifier, "curriculumCode"=>$items[11],"qualityPoints"=>$items[12],"gradeLevel"=>$gradeLevel,"subjName"=>$items[14], "courseNumber"=>$items[15], "courseName"=> $items[16],"daysAttended"=>$items[17], "daysAbsent"=>$items[18], "withdrawalDate"=>findDate($items[19]), "overallSourceCode"=>$overallSourceCode); 
    
    

    return $courses;
}


function csu($items){
    $csu03= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
    );

    $csu07= array(
            "1"=> "Self-contained (Regular Class)", 
            "2"=> "Resource Class" ,
            "3"=> "Separate Class" ,
            "4"=> "Public Separate School Facility" ,
            "5"=> "Private Separate School Facility" ,
            "6"=> "Public Residential Facility" ,
            "7"=> "Private Residential Facility" ,
            "8"=> "Correction Facility" ,
            "9"=> "Homebound or Hospital Environment" ,
            "10"=> "Bilingual Class" ,
            "11"=> "Departmentalized (Regular Class)" ,
            "12"=> "Center-based Instruction" ,
            "13"=> "Televised Instruction" ,
            "14"=> "Individualized Instruction" ,
            "15"=> "Independent Study" ,
            "16"=> "Laboratory" ,
            "17"=> "English as a Second Language (ESL) Class" ,
            "18"=> "Discussion" ,
            "19"=> "Residency" ,
            "20"=> "Internship" ,
            "21"=> "Practicum" ,
            "22"=> "Work Study" ,
            "23"=> "Co-operative Education" ,
            "24"=> "Clinic" ,
            "25"=> 'Lecture and Laboratory' ,
            "26"=> "Lecture and Discussion",
            '27'=> 'Lecture' ,
            '28'=> 'Other' ,
            '29'=> 'Classroom' ,
            '30'=> 'Home Study',
        );
    $csu08= array(
            "A"=>"Adult Credit", 
            "C"=>"Continuing Education", 
            "G"=>"Carnegie Units", 
            "N"=>"NC", 
            "Q"=>"Quarter", 
            "S"=>"Semester", 
            "U"=>"Units", 
            "V"=>"Vocational",
            "X"=>"Other",
        );
    
    
$itemSize = count($items); 
    for($i=$itemSize; $i<=10;$i++){
        $items[$i]="";
    }
    
    
    $dateFormat = isset($csu03[$items[3]]) ? $csu03[$items[3]] : $items[3];
    $dateFormat2 = isset($csu03[$items[5]]) ? $csu03[$items[5]] : $items[5];
    $instructionalCode = isset($csu07[$items[7]]) ? $csu07[$items[7]] : $items[7];
    $creditType = isset($csu08[$items[8]]) ? $csu08[$items[8]] : $items[8];
    $courses = array("subjectPrefix"=>$items[1], "courseNumber"=>$items[2], "dateFormat"=>$dateFormat,"startSate"=>findDateByFormat($dateFormat, $items[4]),"dateFormat2"=>$dateFormat2,"academicGrade"=>findDateByFormat($dateFormat2, $items[6]), "instructionalCode"=>$instructionalCode,"creditType"=>$creditType, "classDuration"=>$items[9], "unitToMeasure"=>$items[10]); 

    return $courses;
}

function mks($items){
    $mks01= array(
            "1"=>"School Mark",
            "2"=>"Department Mark",
            "3"=> "Final Mark",
            "4"=> "Supplemental Mark",
            "5"=> "Mid-term Mark",
            "6"=> "Six-week Mark",
            "7"=>" Examination Mark",
            "8"=> "Work Experience Mark",
            "9"=> "Nine Weeks' Mark", 
            "10"=> "Mark at Time of Withdrawal"
    );

    
    $itemSize = count($items); 
    for($i=$itemSize; $i<=3;$i++){
        $items[$i]="";
    }
    

    $markType = isset($mks01[$items[1]]) ? $mks01[$items[1]] : " ";

    $courses = array("markType"=>$markType, "gradeQualifier"=>$items[2], "grade"=>$items[3]); 

    return $courses;
}
function deg($items){
    $deg01= array(
        "2.1"=>"Postsecondary (less than 1 year)", 
        "2.2"=>"Postsecondary (more than 1 year less than 4)", 
        "2.3"=>"Associate", 
        "2.4"=>"Baccalaureate", 
        "2.5"=>"Baccalaureate", 
        "2.6"=>"Postsecondary (Honors more than 1 year less than 2)", 
        "2.7"=>"Postsecondary (more than 2 years less than 4)", 
        "3.1"=>"First Professional", 
        "3.2"=>"Post-Professional", 
        "4.1"=>"Graduate",
        "4.2"=>"Masters", 
        "4.3"=>"Intermediate Graduate", 
        "4.4"=>"Doctoral", 
        "4.5"=>"Post-Doctoral", 
    );
    $deg02= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
    );
    $deg05 = array(
        "B35"=> "Highest Honors" ,
        "B36"=> "Second Highest Honors" ,
        "B37"=> "Third Highest Honors", 
        "B38"=> "Dropped" ,
        "B39"=> "Academic Probation" ,
        "B40"=> "Suspended" ,
        "D26"=> "Retained in Current Grade" ,
        "D27"=> "Placed in Next Grade After Expected Grade" ,
        "D28"=> "Placed in Transitional Program (K-1)" ,
        "D29"=> "Status Pending Completion of Summer School (K-12)" ,
        "D31"=> "Administratively Placed in a Higher Grade" ,
        "D32"=> "Academically Placed in a Higher Grade" ,
        "D33"=> "Promotion Status not Applicable" ,
        "D34"=> "Promoted" ,
        "EB3"=> "Withdrawn",
    );

    
    $itemSize = count($items); 
    for($i=$itemSize; $i<=5;$i++){
        $items[$i]="";
    }


    $degreeType = isset($deg01[$items[1]]) ? $deg01[$items[1]] : " ";
    $dateFormat = isset($deg02[$items[2]]) ? $deg02[$items[2]] : " ";
    $degreeLevel = isset($deg05[$items[5]]) ? $deg05[$items[5]] : " ";

    $courses = array("degreeType"=>$degreeType, "dateFormat"=>$dateFormat, "date"=>findDateByFormat($dateFormat, $items[3]),  "degreeName"=>ucwords(strtolower($items[4])),"degreeLevel"=>$degreeLevel ); 


    return $courses;
}


function fos($items){
    $fos01= array(
        "C"=> "Concentration" ,
        "E"=> "Endorsement",
        "G"=> "Graduate Non-degree" ,
        "L"=> "Licensing" ,
        "M"=> "Major" ,
        "N"=> "Minor" ,
        "S"=> "Specialization" ,
        "T"=> "Teaching" ,
        "V"=> "Visiting Scholar",
    );
    
    $fos02= array(
        "81"=>"Classification of Instructional Programs (CIP) coding structure maintained by the U.S. Department of Education's National Center for Education Statistics",
        "82"=>"Higher Education General Information Survey (HEGIS) maintained by the U.S. Department of Education's National Center for Education Statistics",
        "CA"=>"Statistics Canada Canadian College Student Information System Course Codes",
        "CC"=>"Statistics Canada University Student Information System Curriculum Codes",
        "ZZ"=> "Mutually Defined ",
        );
    
    $itemSize = count($items); 
    for($i=$itemSize; $i<=7;$i++){
        $items[$i]="";
    }

    $studyLevel = isset($fos01[$items[1]]) ? $fos01[$items[1]] : " ";
    $fieldCode = isset($fos02[$items[2]]) ? $fos02[$items[2]] : " ";
    

    $courses = array("studyLevel"=>$studyLevel, "fieldCode"=>$fieldCode, "identificationCode"=> $items[3],  "description"=>$items[4],"descriptionHonors"=>$items[5],"yearsOfStudy"=>$items[6],"GPA"=>$items[7]); 


    return $courses;
}


function se($items){
    


    $itemSize = count($items); 
    for($i=$itemSize; $i<=2;$i++){
        $items[$i]="";
    }
    

    $courses = array("numberIncludedSegments"=>$items[1], "setControlNumber"=>$items[2]); 
    

    return $courses;
}


function isa($items){

    $is01= array(
            "00"=>"No Authorization Information Present (no info in next field)", 
        );
    $is03= array(
            "00"=>"No Secuirity Information Present (no info in next field)", 
        );
    $is05= array(
            "21"=>"IPEDS",
            "22"=>"FICE", 
            "23"=>"NCES", 
            "24"=>"ATP", 
            "25"=>"ACT", 
            "26"=>"Statistics of Canada List of Postsecondary Institutions", 
            "35"=>"Statistics Canada Canadian College Student Information System Institution Codes", 
            "36"=>"Statistics Canada University Student Information System Institution Code", 
            "ZZ"=>"Mutually Defined",  
        );
    $is10= array(
            "U"=>"United States EDI" 
        );
    $is11= array(
            "00304"=>"Draft Standards Approved for Publication by ASC X12 Procedures Review Board through October 1993 V2",
            "00305"=>"Draft Standards Approved for Publication by ASC X12 Procedures Review Board through October 1996 V3",
            "00401"=>"Draft Standards Approved for Publication by ASC  X12 Procedures Review Board through October  1997 V4 (our Version)",
        );
    $is13= array(
            "0"=>"No Acknowledgment Requested – do not respond with TS99",
            "1"=>"Interchange Acknowledgment Requested – respond with TS997 as soon as the transaction is received",
        );
    $is14= array(
            "T"=>"Test Data",
            "P"=>"Production Data",
        );
$itemSize = count($items); 
    for($i=$itemSize; $i<=14;$i++){
        $items[$i]="";
    }

    $items[1]=str_replace("\\","",$items[1]);
    $items[2]=str_replace("\\","",$items[2]);

    $authQual = isset($is01[$items[1]]) ? $is01[$items[1]] : "Other";
    $secQual = isset($is03[$items[3]]) ? $is03[$items[3]] : "Other";
    $idQual = isset($is05[$items[5]]) ? $is05[$items[5]] : "Other";
    $idQual2 = isset($is05[$items[7]]) ? $is05[$items[7]] : "Other";
    $controlStandards = isset($is10[$items[11]]) ? $is10[$items[11]] : "Other";
    $versionNumber = isset($is11[$items[12]]) ? $is11[$items[12]] : "Other";
    $ackRequested = isset($is13[$items[14]]) ? $is13[$items[14]] : "Other";
    $testIndicator = isset($is14[$items[15]]) ? $is14[$items[15]] : "Other";

    $st = array("authorizationQualifier"=>$authQual, "authorizationInfo"=>$items[2], "securityQualifier"=>$secQual, "securityInfo"=>$items[4], "idQualifier"=>$idQual,"id"=>$items[6], "id"=>$items[6], "idQualifierReciever"=>$idQual2,"idReceiver"=>$items[8],"date"=>findTranscriptDate($items[9]),"time"=>findTime($items[10]), "controlStandards"=>$controlStandards, "versionNumber"=>$versionNumber, "controlNumber" =>$items[13], "ackRequested" =>$ackRequested, "testIndicator" =>$testIndicator); 
    return $st; 
}
function gs($items){

    $gs01= array(
            "ED"=>"Student Educational Record (Transcript) Transaction Set 130", 
            "AK"=>"Student Educational Record (Transcript) Acknowledgment Transaction Set 131",
            "FA"=>"Functional Acknowledgment Transaction Set 997",
            "RY"=>"Request for Student Educational Record (Transcript) Transaction Set 146 ",
            "RZ"=>"Response to Request For Student Educational Record (Transcript) Transaction Set 147",
        );
    $gs08= array(
            "003040"=>"Draft Standards Approved for Publication by ASC X12 Procedures Review Board through October 1993", 
            "003052"=>"Draft Standards Approved for Publication by ASC X12 Procedures Review Board through February 199",
            "004010"=>"Draft Standards Approved for Publication by ASC X12 Procedures Review Board through October 1997",
            "003041ED0020"=>"V2",
            "003052ED0030"=>"V3",
            "004010ED0040"=>"V4 (our guide)",
        );
$itemSize = count($items); 
    for($i=$itemSize; $i<=8;$i++){
        $items[$i]="";
    }

    

    $idCode = isset($gs01[$items[1]]) ? $gs01[$items[1]] : "Other";
    $version = isset($gs08[$items[8]]) ? $gs08[$items[8]] : "Other";


    $st = array("idCode"=>$idCode, "sendersCode"=>$items[2], "receiversCode"=>$items[3], "date"=>findDateByFormat("CCYYMMDD",$items[4]), "time"=>findTime($items[5]),"controlNumber"=>$items[6], "responsibleAgencyCode"=>$items[7], "versionNumber"=>$version); 
    return $st; 
}
function ge($items){

    $st = array("setsIncluded"=>$items[1], "controlNumber"=>$items[2]); 
    return $st; 
}
function st($items){

    $st01= array(
            "130"=>"Transcript", 
        );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=2;$i++){
        $items[$i]="";
    }

    $items[1]=str_replace("\\","",$items[1]);
    $items[2]=str_replace("\\","",$items[2]);

    $transactionType = isset($st01[$items[1]]) ? $st01[$items[1]] : "Other";


    $st = array("transactionType"=>$transactionType, "transactionNumber"=>$items[2]); 
    return $st; 
}

function bgn($items){

    $bgn01= array(
            "00"=>"Original",
            "05"=>"Replace", 
            "07"=>"Duplicate", 
            "11"=>"Response", 
            "15"=>"Re-Submission", 
            "18"=>"Reissue", 
            "ZZ"=>"Mutually Defined",  
        );
    $bgn05= array(
            "AD"=>"Alaska Daylight",
            "AS"=>"Alaska Standard", 
            "CD"=>"Central Daylight", 
            "CT"=>"Central Time", 
            "ED"=>"Eastern Daylight", 
            "ET"=>"Eastern Time", 
            "GM"=>"Greenwich Mean", 
            "HD"=>"Hawaii-Aleutian Daylight", 
            "HS"=>"Hawaii-Aleutian Standard",
            "HT"=>"Hawaii-Aleutian",
            "LT"=>"Local",
            "MD"=>"Mountain Daylight",
            "MS"=>"Mountain Standard",
            "MD"=>"Mountain",
            "ND"=>"Newfoundland Daylight",
            "NS"=>"Newfoundland Standard",
            "NT"=>"Newfoundland",
            "PD"=>"Pacific Daylight",
            "PS"=>"Pacific Standard",
            "PT"=>"Pacific",
            "TD"=>"Atlantic Daylight",
            "TS"=>"Atlantic Standard",
            "TT"=>"Atlantic",
            "UT"=>"Universal",

    );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=5;$i++){
        $items[$i]="";
    }

    $items[1]=str_replace("\\","",$items[1]);
    $items[2]=str_replace("\\","",$items[2]);
    $items[5]=str_replace("\\","",$items[5]);

    $purposeCode = isset($bgn01[$items[1]]) ? $bgn01[$items[1]] : "Other";
    $timeZone = isset($bgn05[$items[5]]) ? $bgn05[$items[5]] : "";


    $st = array("purposeCode"=>$purposeCode, "referenceIdentification"=>$items[2], "date"=>findDate($items[3]),"time"=>findTime($items[4]),"timeZone"=>$timeZone ); 
    return $st; 
}

function erp($items){

    $erp01= array(
            "DP"=>"District to Postsecondary Student Record",
            "PS"=>"PostSecondary Student Academic Record"  
        );
    $erp02= array(
            "053"=>"Notice of Term Enrollment",
            "054"=>"Term Grade Report", 
            "B44"=>"Part of requested record being sent; Remainder to be sent by hard copy", 
            "B48"=>"Record being sent at request of student", 
            "B49"=>"Record being sent to replace one previously sent", 
            "B50"=>"Requested record being sent", 

    );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=3;$i++){
        $items[$i]="";
    }
    $items[1]=str_replace("\\","",$items[1]);
    $items[2]=str_replace("\\","",$items[2]);

    $transactionType = isset($erp01[$items[1]]) ? $erp01[$items[1]] : "Other";
    $reasonCode = isset($erp02[$items[2]]) ? $erp02[$items[2]] : "";




    $erp = array("transactionType"=>$transactionType, "reasonCode"=>$reasonCode, "actionCode"=>$items[3]); 
    return $erp; 
}

function ref($items){

    $ref01= array(
            "N1"=> "Local School Course Number", 
            "N2"=> "Local School District Course Number" ,
            "N3"=> "Statewide Course Number", 
            "N4"=> "United States Department of Education",
            "28"=>"Employee Identification Number",
            "30"=>"U.S.A Visa Number", 
            "48"=>"Agency's Student Number",
            "49"=>"Family Unit Number",
            "4A"=>"Personal Identification Number",
            "50"=>"State Student I.D.",
            "56"=>"Corrected S.S.N.",
            "57"=>"Prior Incorrect S.S.N.",
            "C0"=>"Canadian Social Insurance Number",
            "F8"=>"Original Reference Number",
            "LR"=>"Local Student Identification Number",
            "MV"=>"Migrant Number",
            "SY"=>"SSN",
        );
$itemSize = count($items); 
    for($i=$itemSize; $i<=4;$i++){
        $items[$i]="";
    }
    $items[1]=str_replace("\\","",$items[1]);
    $items[2]=str_replace("\\","",$items[2]);
    $items[3]=str_replace("\\","",$items[3]);
    $items[4]=str_replace("\\","",$items[4]);

    $referenceQualifier = isset($ref01[$items[1]]) ? $ref01[$items[1]] : "Other";


    $ref = array("referenceQualifier"=>$referenceQualifier, "referenceID"=>$items[2], "description"=>$items[3],"referenceIdentifier"=>$items[4]); 
    return $ref; 
}

function dmg($items){

    $dmg01= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
        );
    $dmg03= array(
            "F"=>"Female",
            "M"=>"Male", 
            "U"=>"Unknown",
        );
    $dmg04= array(
            "A"=>"Female",
            "B"=>"Male", 
            "D"=>"Divorced",
            "I"=>"Single",
            "K"=>"Unknown",
            "M"=>"Married",
            "R"=>"Unreported",
            "S"=>"Seperated",
            "U"=>"Unmarried",
            "W"=>"Widowed",
            "X"=>"Legally Seperated",
        );
    $dmg05= array(
            "7"=>"Not Provided",
            "A"=>"Asian or Pacific Islander",
            "B"=>"Black", 
            "C"=>"Caucasian",
            "D"=>"Subcontinent Asian American",
            "E"=>"Other Race Or Ethnicity",
            "F"=>"Asian Pacific American",
            "G"=>"Native American",
            "H"=>"Hispanic",
            "I"=>"American Indian or Alaskan Native",
            "J"=>"Native Hawaiian",
            "N"=>"Black (Non-Hispanic)",
            "O"=>"White (Non-Hispanic)",
            "P"=>"Pacific Islander",
            "Z"=>"Mutually Defined",
        );
    $dmg06= array(
            "1"=>"U.S. Citizen",
            "2"=>"Non-Resident Alien",
            "3"=>"Resident Alien", 
            "4"=>"Illegal Alien",
            "5"=>"Alien",
            "6"=>"U.S. Citizzen-Non-Resident",
            "7"=>"U.S. Citizzen-Resident",
            "8"=>"Citizen",
            "9"=>"Non-Citizen with Student Authorization",
            "A"=>"Non-Permanent Resident Alien",
            "B"=>"Permanent Visa",
            "C"=>"Temporary Visa",
        );

$itemSize = count($items); 
    for($i=$itemSize; $i<=8;$i++){
        $items[$i]="";
    }

    $dateFormat = isset($dmg01[$items[1]]) ? $dmg01[$items[1]] : "Other";
    $gender = isset($dmg03[$items[3]]) ? $dmg03[$items[3]] : "Other";
    $maritalStatus = isset($dmg04[$items[4]]) ? $dmg04[$items[4]] : $items[4];
    $race = isset($dmg05[$items[5]]) ? $dmg05[$items[5]] : $items[5];
    $residency = isset($dmg06[$items[6]]) ? $dmg06[$items[6]] : $items[6];


    $dmg = array("dateFormat"=>$dateFormat, "dob"=>findDateByFormat($dateFormat,$items[2]), "gender"=>$gender,"maritalStatus"=>$maritalStatus, "race"=>$race, "residency"=>$residency, "countryCode"=>$items[7], "basisVerificationCode"=>$items[8], "quantity"=>$items[8]); 
    return $dmg; 
}

function lui($items){

    $lui01= array(
            "LD"=>"NISO Z39.53",
            "LE"=>"LE ISO 639", 
        );
    $lui04= array(
            "1"=> "Language of Instruction" ,
            "2"=> "Language of Examination", 
            "3"=> "Language in which Examination is Written",
            "4"=>"Status Unknown",
            "5"=>"Excellent or Fluent", 
            "6"=>"Good",
            "7"=>"Fair",
            "8"=>"Unacceptable",
    );

$itemSize = count($items); 
    for($i=$itemSize; $i<=4;$i++){
        $items[$i]="";
    }




    $languageCodeQualifier = isset($lui01[$items[1]]) ? $lui01[$items[1]] :$items[1];
    $languageIndicator = isset($lui04[$items[4]]) ? $lui04[$items[4]] :$items[4];
    $lui = array("languageCodeQualifier"=>$languageCodeQualifier,"languageCode"=>$items[2], "languageName"=>$items[3], "languageIndicator"=>$languageIndicator); 
    return $lui; 
}

function ind($items){

$itemSize = count($items); 
    for($i=$itemSize; $i<=12;$i++){
        $items[$i]="";
    }
    $ind = array("birthCountry"=>$items[1],"birthStateCode"=>$items[2], "birthCounty"=>$items[3], "birthCity"=>$items[4],"languageCode"=>$items[5],"languageProficiency"=>$items[6],"languageCode"=>$items[7], "languageCodeCorrespondence"=>$items[8],"indentificationCodeQualifer"=>$items[9], "identificationCode"=>$items[10], "indentificationCodeQualifer"=>$items[11], "identificationCode"=>$items[12]); 
    return $ind; 
}

function dtp($items){
    $dtp01= array(
            "AAA"=>"Arrival in Country",
            "ACA"=>"Immigration Date", 
            "ACB"=>"Estimated Immigration Date", 
            "007"=> "Effective" ,
            "036"=> "Expiration" ,
            "043"=> "Publication" ,
            "050"=> "Received" ,
            "055"=> "Confirmed" ,
            "102"=> "Issue" ,
            "103"=> "Award" ,
            "196"=> "Start" ,
            "197"=> "End" ,
            "198"=> "Completion", 
            "237"=> "Student Signed", 
            "270"=> "Date Filed",
            "275"=> "Approved" ,
            "336"=> "Employment Begin" ,
            "337"=> "Employment End" ,
            "467"=> "Signature" ,
            "574"=> "Action Begin Date" ,
            "576"=> "Action End Date" ,
            "ZZZ"=> "Mutually Defined",
        );
    $dtp02= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD", 
            "DB"=>"MMDDCCYY", 
            "RD4"=>"CCYY-CCYY",
            "RD5"=>"CCYYMM-CCYYMM",
            "RD8"=>"CCYYMMDD-CCYYMMDD",
        );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=3;$i++){
        $items[$i]="";
    }
    
    $dateType = isset($dtp01[$items[1]]) ? $dtp01[$items[1]] : "None";
    $dateFormat = isset($dtp02[$items[2]]) ? $dtp02[$items[2]] : "None";
    
    $ind = array("dateType"=>$dateType,"dateFormat"=>$dateFormat, "date"=>findDateByFormat($dateFormat,$items[3]));
    return $ind; 
}
function rap($items){
    $rap04= array(
            "A"=>"Attribute",
            "P"=>"Proficiency", 
            "R"=>"Requirement", 
        );
    $rap05= array(
            "N"=>"No",
            "Y"=>"Yes", 

        );
    $rap06= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD", 
            "DB"=>"MMDDCCYY", 
        );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=6;$i++){
        $items[$i]="";
    }

    $usuageIndicator = isset($rap04[$items[4]]) ? $rap04[$items[4]] : "None";
    $proficient = isset($rap05[$items[5]]) ? $rap05[$items[5]] : "";
    $dateFormat = isset($rap06[$items[6]]) ? $rap06[$items[6]] : "";
    $testCode = isset($items[1]) ? $items[1] : "";
    $name1 = isset($items[2]) ? $items[2] : "";
    $name2 = isset($items[3]) ? $items[3] : "";
    $dateItem = isset($items[7]) ? $items[7] : "";
    
    $ind = array("EducationalTestCode"=>$testCode,"Name1"=>$name1, "Name2"=>$name2, "usuageIndicator" => $usuageIndicator, "proficient"=> $proficient, "dateFormat"=> $dateFormat , "date"=>findDateByFormat($dateFormat,$dateItem));
    return $ind; 
}





function pcl($items){

    $pcl01= array(
            "71"=>"IPEDS", 
            "72"=>"ATP", 
            "73"=>"FICE", 
            "74"=>"ACT", 
            "CB"=>"Canada College Student", 
            "CS"=>"Canada University Student", 
        );
    $pcl03= array(
            "RD4"=>"CCYY-CCYY", 
            "RD5"=>"CCYYMM-CCYYMM", 
            "RD8"=>"CCYYMMDD-CCYYMMDD", 
        );
    $pcl05= array(
            "2.1"=>"Postsecondary (less than 1 year)", 
            "2.2"=>"Postsecondary (more than 1 year less than 4)", 
            "2.3"=>"Associate", 
            "2.4"=>"Baccalaureate", 
            "2.5"=>"Baccalaureate", 
            "2.6"=>"Postsecondary (Honors more than 1 year less than 2)", 
            "2.7"=>"Postsecondary (more than 2 years less than 4)", 
            "3.1"=>"First Professional", 
            "3.2"=>"Post-Professional", 
            "4.1"=>"Graduate",
            "4.2"=>"Masters", 
            "4.3"=>"Intermediate Graduate", 
            "4.4"=>"Doctoral", 
            "4.5"=>"Post-Doctoral",  
        );

    $itemSize = count($items); 
    for($i=$itemSize; $i<=7;$i++){
        $items[$i]="";
    }

    $institutionQualifier = isset($pcl01[$items[1]]) ? $pcl01[$items[1]] : "Other";
    $dateFormat = isset($pcl03[$items[3]]) ? $pcl03[$items[3]] : ""; 
    $degreeLevel = isset($pcl05[$items[5]]) ? $pcl05[$items[5]] : "";  
    $temDate= explode("-", $items[4]);
    $temDate[0] =isset($temDate[0]) ? $temDate[0] : ""; 
    $temDate[1] =isset($temDate[1]) ? $temDate[1] : "";  
    $from =  findDate($temDate[0]);
    $to =  findDate($temDate[1]);
    $dateAttended = array("from"=>$from, "to"=>$to);


    $colleges = array("institutionQualifier"=>$institutionQualifier, "institutionCode"=>$items[2], "dateFormat"=>$dateFormat,"dateAttended"=>findDateByFormat($dateFormat, $items[4]),"degreeLevel"=>$items[5], "dateOfDegree"=>$items[6], "institutionName"=>ucwords(strtolower($items[7]))); 
    return $colleges; 
    }


function nte($items){

$itemSize = count($items); 
    for($i=$itemSize; $i<=2;$i++){
        $items[$i]="";
    }

    $colleges = array("noteCode"=>$items[1],"noteDescription"=>$items[2]); 
    return $colleges; 
    }
function n1($items){
    $n101= array(
            "AS"=>"Postsecondary Education Sender",
            "AT"=>"PostSecondary Education Recipient", 
            "KS"=>"Pre-Kindergarten to Grade 12 Sender",
            "HS"=> "High School",
            "M8"=> "Educational Institution",
            "VO"=> "Elementary School",
            "VQ"=> "Middle School",
            "VR"=> "Junior High School",
            "ZZ"=> "Mutually Defined ",
            "OS"=> "Override Institution; this is not the institution sending the record",
        );
    $n103= array(
            "71"=>"IPEDS", 
            "72"=>"ATP", 
            "73"=>"FICE", 
            "74"=>"ACT",
            "77"=> "National Center for Education Statistics (NCES) Common Core of Data (CCD) number for PreK - 12 institutions" ,
            "78"=> "The College Board and ACT 6 digit code list of secondary educational institutions", 
            "CB"=>"Canada College Student", 
            "CS"=>"Canada University Student", 
        );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=6;$i++){
        $items[$i]="";
    }

    $identifierCode = isset($n101[$items[1]]) ? $n101[$items[1]] : "None";
    $indentificationCodeQualifer = isset($n103[$items[3]]) ? $n103[$items[3]] : "";
    
    $ind = array("identifierCode"=>$identifierCode,"institutionName"=>$items[2], "indentificationCodeQualifer"=>$indentificationCodeQualifer, "institutionCode"=>$items[4], "entityRelationshipCode"=>$items[5], "entityIdentifierCode"=>$items[6]);
    return $ind; 
}
function n2($items){

$itemSize = count($items); 
    for($i=$itemSize; $i<=2;$i++){
        $items[$i]="";
    }
    
    $ind = array("Name1"=>$items[1],"Name2"=>$items[2]);
    return $ind; 
}
function n3($items){
$itemSize = count($items); 
    for($i=$itemSize; $i<=2;$i++){
        $items[$i]="";
    }

    
    $ind = array("Address1"=>$items[1],"Address2"=>$items[2]);
    return $ind; 
}
function n4($items){

    $n405= array(
            "DT"=>"Domicile Type Code",
            "F"=>"Current Address", 
            "H"=>"Home Address", 
            "I"=>"Home Base Address", 
            "L"=>"Local Address", 
            "M"=>"Mailing Address", 
            "O"=>"Office Address", 
            "P"=>"Permanent Address", 
            "AC"=> "City and State" ,
            "CC"=>"Country",
            "CI"=> "City" ,
            "CY" =>"County/Parish" ,
            "DR"=> "District of Residence" ,
            "PT"=> "3 Digit Canadian Postal Code" ,
            "PU"=> "6 Digit Canadian Postal Code",
            "RE" =>"Regional Education Service Agency",
            "SD"=> "School District" ,
            "SH"=> "School Campus Code", 
            "SP"=> "State/Province" ,
            "SS"=> "School" ,
            "TN"=> "Township",
            "ZZ"=> "Mutually Defined",
        );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=6;$i++){
        $items[$i]="";
    }
    
    $locationQualifier = isset($n405[$items[5]]) ? $n405[$items[5]] : "";
    
    $ind = array("cityName"=>$items[1],"state"=>$items[2], "postalCode"=>$items[3], "country"=>$items[4], "locationQualifier"=>$locationQualifier, "locationIdentifier"=>$items[6]);
    return $ind; 
}

function per($items){
    $per01= array(
            "BP"=>"School Principal",
            "DN"=>"Dental School Admission Office", 
            "E2"=>"Evening Programs Office", 
            "FA"=>"Financial Aid Office", 
            "GA"=>"Graduate Fine Arts Office", 
            "GB"=>"Graduate Business Office", 
            "GC"=>"Guidance Counselor", 
            "GE"=>"Graduate Engineering Office", 
            "GR"=>"Graduate Admissions Office", 
            "LD"=>"Law School Admissions Office", 
            "MD"=>"Medical Admissions Office", 
            "PK"=>"Performance Evaluation Committee", 
            "PS"=>"Personnel Department", 
            "RG"=>"Registrar", 
            "SB"=>"Student", 
            "SK"=>"School Clerk", 
            "SP"=>"Special Program Contact", 
            "SW"=>"Social Services Worker", 
            "TC"=>"College of Education Admissions Office", 
            "TH"=>"School of Theology Admissions Office", 
            "UG"=>"Undergraduate Admissions Office", 
            "VM"=>"School of Veterinary Medicine Admissions Office",  
        );
    $per03= array(
            "AP"=>"Alternate Telephone ", 
            "AS"=>"Answering Service", 
            "BN"=>"Beeper Number ", 
            "CP"=>"Cellular Phone", 
            "EM"=>"Electronic Mail", 
            "EX"=>"Telephone Extension", 
            "FX"=>"Facsimile", 
            "HF"=>"Home Facsimile Number", 
            "HP"=>"Home Phone Number ", 
            "NP"=>"Night Telephone", 
            "OF"=>"Other Residential Facsimile Number", 
            "OT"=>"Other Residential Telephone Number", 
            "PA"=>"Appointment Phone", 
            "PC"=>"Personal Cellular ", 
            "PP"=>"Personal Phone", 
            "TE"=>"Telephone", 
            "TL"=>"Telex", 
            "TM"=>"Telemail", 
            "TN"=>"Teletex Number", 
            "VM"=>"Voice Mail", 
            "WC"=>"Work Cellular", 
            "WF"=>"Work Facsimile Number",
            "WP"=>"Work Phone Number",  
        );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=9;$i++){
        $items[$i]="";
    }

    $contact = isset($per01[$items[1]]) ? $per01[$items[1]] : "None";
    $communicationType1 = isset($per03[$items[3]]) ? $per03[$items[3]] : "";
    $communicationType2 = isset($per03[$items[5]]) ? $per03[$items[5]] : "";
    $communicationType3 = isset($per03[$items[7]]) ? $per03[$items[7]] : "";
    $ind = array("contact"=>$contact,"contactName"=>$items[2], "communicationType1"=>$communicationType1, "communicationNumber"=>$items[4] , "communicationType2"=>$communicationType2, "communicationNumber2"=>$items[6], "communicationType3"=>$communicationType3, "communicationNumber3"=>$items[8], "additionalInfo"=>$items[9]);
    return $ind; 
}

function in1($items){
    $in101= array(
            "1"=>"Person",
            "2"=>"Non-Person",
        );
    $in102= array(
            "01"=>"Given Name", 
            "02"=>"Current Legal", 
            "03"=>"Alias", 
            "04"=>"Name of Record", 
            "05"=>"Previous Name", 
            "07"=>"Married Name", 
            "08"=>"Professional Name", 
        );
    $in103= array(
            "6X"=>"Disciplinary Contact", 
            "E1"=>"Person or Other Entity Legally Responsible for a Child", 
            "E2"=>"Person or Other Entity With Whom a Child Resides", 
            "E3"=>"Person or Other Entity Legally Responsible for and With Whom a Child Resides", 
            "E4"=>"Other Person or Entity Associated with Student", 
            "S1"=>"Parent",
            "S2"=>"Student", 
            "S3"=>"Custodial Parent",  
        );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=7;$i++){
        $items[$i]="";
    }
    
    $personType = isset($in101[$items[1]]) ? $in101[$items[1]] : "None";
    $nameType = isset($in102[$items[2]]) ? $in102[$items[2]] : "None";
    $guardianType = isset($in103[$items[3]]) ? $in103[$items[3]] : "";
    
    $ind = array("personType"=>$personType,"nameType"=>$nameType, "guardianType"=>$guardianType, "referenceQualifier"=>$items[4], "referenceIdentifier"=>$items[5], "individualRelationshipCode"=>$items[6],"individualLevel"=>$items[7]);
    return $ind; 
}



function in2($items){
    $in201= array(
            "01"=>"Prefix",
            "02"=>"First Name",
            "03"=>"First Middle Name",
            "04"=>"Second Middle Name",
            "05"=>"Last Name",
            "06"=>"First Initital",
            "07"=>"First Middle Initital",
            "08"=>"Second Middle Initital",
            "09"=>"Suffix",
            "12"=>"Combined (Unstructured) Name",
            "14"=>"Name of an Agency",
            "15"=>"Maiden or former Name",
            "16"=>"Composite Name",
            "17"=>"Middle Names",
            "18"=>"Preferred First Name or Nickname",
            "22"=>"Organization Name"
        );

    $componentName = isset($in201[$items[1]]) ? $in201[$items[1]] : "None";

    
    $ind = array("componentName"=>$componentName,"name"=>$items[2]);
    return $ind; 
}
function sst($items){
    $sst01= array(
            "B17"=>"Did not complete secondary school",
            "B18"=>"Standard high school diploma",
            "B19"=>"Advanced or honors diploma",
            "B20"=>"Vocational Diploma",
            "B21"=>"Special education diploma",
            "B22"=>"Certificate of completition",
            "B23"=>"Special certificate of completition",
            "B24"=>"General Education Development Diploma (GED)",
            "B25"=>"Other high school equivalency diploma",
            "B26"=>"International diploma or certificate (such as International Baccalaureate)",
            
        );
    $sst02= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
        );
    $sst04= array(
            "B27"=>"Student is eligible to continue or return or both",
            "B28"=>"Student is on suspension or dismissal", 
            "B29"=>"Student is expelled (from PreK - grade 12)",
            "B51"=>"Student on Suspension or Dismissal; Eligible to Apply for Reentry",
        );
    $sst07= array(
        "B30"=> "Currently enrolled but courses in progress not included",
        "B31"=> "Not currently enrolled",
        "B33"=> "Unreported - Information is not available in record",
        "B34"=> "Currently enrolled and courses in progress are included",
    );

    $sst08= array( 
        "08"=>"8",
        "09"=>"9",
        "0K"=>"Kindergarten",
        "10"=>"10",
        "11"=>"11",
        "12"=>"12",
        "AD"=>"Adult",
        "HG"=>"High School Graduate or Equivalent",
        "HS"=>"Attended high school, but did not graduate",
        "PS"=>"Some Postsecondary",
        "SS"=>"Secondary School",
        "VS"=>"Vocational School",
    );  
    $sst09= array( 
        "N"=>"No",
        "U"=>"Unknown",
        "Y"=>"Yes",
    );  

$itemSize = count($items); 
    for($i=$itemSize; $i<=9;$i++){
        $items[$i]="";
    }



    $highSchoolGraduation = isset($sst01[$items[1]]) ? $sst01[$items[1]] : "None";
    $dateFormat = isset($sst02[$items[2]]) ? $sst02[$items[2]] : "None";
    $returnCode = isset($sst04[$items[4]]) ? $sst04[$items[4]] : "None";
    $dateFormat2 = isset($sst02[$items[5]]) ? $sst02[$items[5]] : "None";
    $enrollment = isset($sst07[$items[7]]) ? $sst07[$items[7]] : "None";
    $studentsGradeLevel = isset($sst08[$items[8]]) ? $sst08[$items[8]] : "None";
    $resident = isset($sst09[$items[9]]) ? $sst09[$items[9]] : "None";

    
    $ind = array("highSchoolGraduation"=>$highSchoolGraduation,"dateFormat"=>$dateFormat, "HighSchoolGraduationDate"=>findDateByFormat($dateFormat,$items[3]), "returnCode"=>$returnCode, "dateFormat2"=>$dateFormat2, "eligibilityReturnDate"=>findDateByFormat($dateFormat2,$items[6]), "enrollment"=>$enrollment, "studentsGradeLevel"=>$studentsGradeLevel, "resident"=>$resident);
    return $ind; 
}
function sse($items){
    
    $sse03= array(
        "B27"=>"Student is eligible to continue or return or both",
        "B28"=>"Student is on suspension or dismissal", 
        "B29"=>"Student is expelled (from PreK - grade 12)",
        "B31"=> "Not currently enrolled",
        "B38"=> "Dropped",
        "B39"=> "Academic Probation",
        "B40"=> "Suspended",
        "B51"=>"Student on Suspension or Dismissal; Eligible to Apply for Reentry",
        "B52"=> "According to established regulations or statutes",
        "D03"=> "Student has attended a nonpublic school or home education program in- or out-of-state this year",
        "D04"=> "Student was received from another attendance reporting unit in the same school",
        "D05"=> "Student was received from a school in the same district" ,
        "D06"=> "Student was received from another public school outside the district either in- or out-of-state ",
        "D07"=> "Student was received from a nonpublic school either in or out of the district or has returned after having been enrolled in a home education program; The student must have been enrolled previously in a public school this year",
        "D08"=> "Student unexpectedly reentered the same school after withdrawing or being discharged", 
        "D09"=> "Student was expected to attend a school but did not enter as expected for unknown reasons",
        "D10"=> "Student was promoted, retained, or transferred to another attendance-reporting unit in the same school",
        "D11"=> "Student was promoted, retained, or transferred to another school in the same district",
        "D12"=> "Student withdrew to attend another public school in the same district",
        "D13"=> "Student withdrew to attend another public school in- or out-ofstate",
        "D14"=> "Student Over Compulsory Attendance Age Left School Voluntarily with No Intention of Returning" ,
        "D15"=> "Student Graduated from School with a Standard Diploma", 
        "D16"=> "Student Graduated from School with a Special Diploma",
        "D17"=> "Student Left School with a Certificate of Completion", 
        "D18"=> "Student Left School with a Special Certificate of Completion",
        "D19"=> "Student Left School with a State General Education Development (GED) High School Diploma",
        "D20"=> "Student Withdrew to Attend a Non-Public School or Home Education Program In- or Out-of-State.",
        "D21"=> "Student withdrew from school due to hardship",
        "D22"=> "Student has not entered any school in this or any other state this school year",
        "D23"=> "Previously attended out-of-state public school but is entering a public school in this state for the first time this school year",
        "D24" =>"Returned to Regular Education Program ",
        "D53"=> "Graduate from a College" ,
        "D54"=> "Transfer from a University Program" ,
        "D55"=> "Graduate from a University Program" ,
        "D56"=> "Exchange Student" ,
        "D57"=> "Returning Student Admitted to a New Program" ,
        "D58"=> "Returning Student Admitted to the Same Program" ,
        "D59"=> "Returning or Continuing Student Changing to Unclassified or General or Unspecified Studies" ,
        "D60" =>"Continuing Student Changing to a New Program" ,
        "D61"=> "Special Permission" ,
        "D62"=> 'Graduate from a Technical Institute',
        "D63" =>'Transfer from a College',
        "EB1"=> "Deceased",
        "EB3"=> "Withdrawn",
        "EB4"=> "Graduated",
    );



$itemSize = count($items); 
    for($i=$itemSize; $i<=4;$i++){
        $items[$i]="";
    }




    $reasonCode = isset($sse03[$items[3]]) ? $sse03[$items[3]] : "None";


    
    $ind = array("entryDate"=>findDate($items[1]),"exitDate"=>findDate($items[2]), "reasonCode"=>$reasonCode,  "priority"=>$items[4] );
    return $ind; 
}


function atv($items){
    
    $atv01= array(
        "SA"=>"Student Activity Type Code",
        "SB"=>"Student Award Code"
    );




$itemSize = count($items); 
    for($i=$itemSize; $i<=10;$i++){
        $items[$i]="";
    }



    $activityQualifier = isset($atv01[$items[1]]) ? $atv01[$items[1]] : "None";


    
    $ind = array("activityQualifier"=>$activityQualifier,"industryCode"=>$items[2], "awardTitle"=>$items[3], "organizationTitle"=>$items[4], "quantity"=>$items[5], "unitToMeasure"=>$items[6], "participationLevel"=>$items[7], "paid"=>$items[8], "schoolSponsored"=>$items[9], "recruited"=>$items[10] );
    return $ind; 
}
function tst($items){
    $tst03= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
        );
    $tst07= array(
        "01"=>"1", 
        "02"=>"2", 
        "03"=>"3", 
        "04"=>"4", 
        "05"=>"5", 
        "06"=>"6", 
        "07"=>"7", 
        "08"=>"8",
        "09"=>"9",
        "0K"=>"Kindergarten",
        "10"=>"10",
        "11"=>"11",
        "12"=>"12",
        "20"=>"Non-Degree or Temporary Undergraduate in Postsecondary",
        "21"=>"PostSecondary First Year",
        "22"=>"PostSecondary Sophmore",
        "23"=>"PostSecondary Junior",
        "24"=>"PostSecondary Senior",
        "25"=>"Post-Bacc",
        "26"=>"Non-Degree Graduate",
        "27"=>"Professional",
        "28"=>"Masters",
        "29"=>"Doctoral",
        "30"=>"Postdoctoral",
        "31"=>"Bachelor Preliminary",
        "32"=>"Fifth year",
        "33"=>"Masters Qualifying Year",
        "AD"=>"Adult",
        "AS"=>"Associate Degree",
        "BD"=>"Baccalaureate (Bachelor's) Degree",
        "DD"=>"Doctoral Degree",
        "EL"=>"Elementary",
        "HG"=> "High School Graduate or Equivalent",
        "HS"=> "Attended high school, but did not graduate",
        "IF"=>"Infant",
        "MD"=>"Masters Degree",
        "MS"=>"Middle/Junior School",
        "P0"=>"Pre-Kindergarten L0",
        "P1"=>"Pre-Kindergarten L1",
        "P2"=>"Pre-Kindergarten L2",
        "P3"=>"Pre-Kindergarten L3",
        "P4"=>"Pre-Kindergarten L4",
        "P5"=>"Pre-Kindergarten L5",
        "PC"=> "Postsecondary Certificate or Diploma",
        "PD"=> "Professional Degree or Certification",
        "PF" =>'Professional',
        "PK"=>"Pre-Kindergarten",
        "PS"=>"Some Postsecondary",
        "SS"=>"Secondary School",
        "UN"=>"Ungraded",
        "VS"=>"Vocational School",
    );


$itemSize = count($items); 
    for($i=$itemSize; $i<=15;$i++){
        $items[$i]="";
    }





    $dateFormat = isset($tst03[$items[3]]) ? $tst03[$items[3]] : "None";
    $individualLevel = isset($tst07[$items[7]]) ? $tst07[$items[7]] : "None";


    
    $ind = array("testCode"=>$items[1],"testName"=>$items[2], "dateFormat"=>$dateFormat, "testDate"=>findDateByFormat($dateFormat, $items[4]), "testForm"=>$items[5], "testLevel"=>$items[6], "individualLevel"=>$individualLevel, "testGradeLevel"=>$items[8], "dateNormYear"=>$items[9], "testNormType"=>$items[10], "testNormingPeriodCode"=>$items[11], "languageCode"=>$items[12], "dateTimePeriod"=>$items[13], "testRevised"=>$items[14], "testInvalidated"=>$items[15] );
    return $ind; 
}

function sbt($items){
    $itemSize = count($items); 
    for($i=$itemSize; $i<=3;$i++){
        $items[$i]="";
    }


    $ind = array("subTestCode"=>$items[1],"testName"=>$items[2], "interpretationCode"=>$items[3]);
    return $ind; 
}
function sre($items){
    
    $sre01= array(
        "1"=>"Scaled Score",
        "2"=>"Grade Equivalent or Grade Level Indicator", 
        "3"=>"Standard Score",
        "4"=>"Raw Score",
        "5"=>"Percent of Items Corrected",
        "6"=>"Mastery Score",
        "7"=>"Adjective Classification or Locally Defined Score",
        "8"=>"Stanine",
        "9"=>"Percentile",
        "A"=>"Normal Curve Equivalent",
        "B"=>"Equated Score",
        "Z"=>"Locally Defined",
    );
    $itemSize = count($items); 
    for($i=$itemSize; $i<=2;$i++){
        $items[$i]="";
    }

    $scoreType = isset($sre01[$items[1]]) ? $sre01[$items[1]] : "None";
    $ind = array("scoreType"=>$scoreType,"description"=>$items[2]);
    return $ind; 
}
function sum($items){
    
    $sum01= array(
            "A"=>"Adult Credit", 
            "C"=>"Continuing Education", 
            "G"=>"Carnegie Units", 
            "N"=>"NC", 
            "Q"=>"Quarter", 
            "S"=>"Semester", 
            "U"=>"Units", 
            "V"=>"Vocational",
            "X"=>"Other",
        );
    $sum02= array(
        "1"=>"Remedial", 
        "2"=>"Basic", 
        "3"=>"TA", 
        "4"=>"General", 
        "5"=>"Applied", 
        "6"=>"Survey", 
        "7"=>"Regular", 
        "8"=>"Spec. Top.",
        "9"=>"Adv.",
        "10"=>"Honors",
        "11"=>"Gifted",
        "12"=>"AP",
        "13"=>"Special Ed.",
        "14"=>"Vocational",
        "15"=>"Ind. Study",
        "16"=>"Work Experience",
        "17"=>"Adult Basic",
        "18"=>"Adult Secondary",
        "19"=>"IB",
        "A"=>"Summary of all courses taken at all institutions",
        "AR"=>"Academic Renewal",
        "B"=>"Summary of all courses taken at sending institution",
        "D"=>"Dual Level",
        "DL"=>"Dual Level",
        "E"=>"Summary of All courses Taken at All institutions. Excluding Repeated and/or Forgiven courses",
        "F"=>"Summary of All Courses Taken at the Sending Institution, Excluding Repeated and/or Forgiven Courses",
        "G"=>"Grad",
        "H"=>"Higher",
        "I"=>"Institutional Credit",
        "L"=>"Lower",
        "M"=>"Work in the Major",
        "P"=>"Professional",
        "R"=>"Remedial",
        "T"=>"Summary of Transfer Work Only",
        "U"=>"Undergraduate",
        "V"=>"Summary of Transfer Work Only, Excluding Repeated and/or Forgiven Courses",
    );   
    $sum03= array(
            "Y"=>"Yes",
            "N"=>"No", 
        );
    $sum10= array(
            "Y"=>"Yes", 
            "N"=>"No", 
        );
    $sum13= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
        );
$itemSize = count($items); 
    for($i=$itemSize; $i<=18;$i++){
        $items[$i]="";
    }

    $creditType = isset($sum01[$items[1]]) ? $sum01[$items[1]] : "None";
    $courseLevel = isset($sum02[$items[2]]) ? $sum02[$items[2]] : "None";
    $cumulativeScore = isset($sum03[$items[3]]) ? $sum03[$items[3]] : "None";
    $excessiveGPA = isset($sum10[$items[10]]) ? $sum10[$items[10]] : "None";
    $dateFormat = isset($sum13[$items[13]]) ? $sum13[$items[13]] : "None";
    $ind = array("creditType"=>$creditType,"courseLevel"=>$courseLevel, "cumulativeScore"=>$cumulativeScore, "creditHoursGPA"=>$items[4], "creditHoursAttempted"=>$items[5], "creditHoursEarned"=>$items[6], "lowestPossibleGradeAverage"=>$items[7], "maximumPossibleGradeAverage"=>$items[8], "gpa"=>$items[9], "excessiveGPA"=>$excessiveGPA, "classRank"=>$items[11], "studentInClass"=>$items[12], "dateFormat"=>$dateFormat, "date"=>findDateByFormat($dateFormat,$items[14]),  "daysAttended"=>$items[15], "daysAbsent"=>$items[16],  "pointsForGPA"=>$items[17], "academicSummary"=>$items[18]);
    
    if(!preg_match('/^[0-9]{1,3}.[0-9]{1,1}+$/',$ind['creditHoursAttempted'])) {//attempted credits
        $arr = explode(".", $ind['creditHoursAttempted']);
        if(isset($arr[1])) {
            if(strlen($arr[1]) > 1) {
                $arr[1] = $arr[1][0];
            }
            $ind['creditHoursAttempted'] = implode(".",$arr);
        }
        else {
            $ind['creditHoursAttempted'] = $ind['creditHoursAttempted'].".0";
        }
    }

    if(!preg_match('/^[0-9]{1,3}.[0-9]{1,1}+$/',$ind['creditHoursEarned'])) {//earned credits
        $arr = explode(".", $ind['creditHoursEarned']);
        if(isset($arr[1])) {
            if(strlen($arr[1]) > 1) {
                $arr[1] = $arr[1][0];
            }
            $ind['creditHoursEarned'] = implode(".",$arr);
        }
        else {
            $ind['creditHoursEarned'] = $ind['creditHoursEarned'].".0";
        }
    }
    if($ind['lowestPossibleGradeAverage'] != "") {
        if(!preg_match('/^[0-9]{1,3}.[0-9]{1,1}+$/',$ind['lowestPossibleGradeAverage'])) {//lowest possible
            $arr = explode(".", $ind['lowestPossibleGradeAverage']);
            if(isset($arr[1])) {
                if(strlen($arr[1]) > 1) {
                    $arr[1] = $arr[1][0];
                }
                $ind['lowestPossibleGradeAverage'] = implode(".",$arr);
            }
            else {
                $ind['lowestPossibleGradeAverage'] = $ind['lowestPossibleGradeAverage'].".0";
            }
        }
    }
    if($ind['maximumPossibleGradeAverage'] != "") {
        if(!preg_match('/^[0-9]{1,3}.[0-9]{1,1}+$/',$ind['maximumPossibleGradeAverage'])) {//max possible
            $arr = explode(".", $ind['maximumPossibleGradeAverage']);
            if(isset($arr[1])) {
                if(strlen($arr[1]) > 1) {
                    $arr[1] = $arr[1][0];
                }
                $ind['maximumPossibleGradeAverage'] = implode(".",$arr);
            }
            else {
                $ind['maximumPossibleGradeAverage'] = $ind['maximumPossibleGradeAverage'].".0";
            }   
        }
    }//Unsure whether points for gpa should have two decimal places or one, leaving it for now
//Unsure whether points for gpa should have two decimal places or one, leaving it for now
    if(!preg_match('/^[0-9]{1,3}.[0-9]{1,1}+$/',$ind['pointsForGPA'])) {//points for gpa
        $arr = explode(".", $ind['pointsForGPA']);
        if(isset($arr[1])) {
            if(strlen($arr[1]) > 1) {
                $arr[1] = $arr[1][0];
            }
            $ind['pointsForGPA'] = implode(".",$arr);
        }
        else {
            $ind['pointsForGPA'] = $ind['gpa'].".0";
        }
    }
    if(!preg_match('/^[0-9]{1,3}.[0-9]{1,1}+$/',$ind['creditHoursGPA'])) {//points for gpa
        $arr = explode(".", $ind['creditHoursGPA']);
        if(isset($arr[1])) {
            if(strlen($arr[1]) > 1) {
                $arr[1] = $arr[1][0];
            }
            $ind['creditHoursGPA'] = implode(".",$arr);
        }
        else {
            $ind['creditHoursGPA'] = $ind['creditHoursGPA'].".0";
        }
    }
    return $ind; 
}

function lx($items){



    $colleges = array("assignedNumber"=>$items[1]); 
    return $colleges; 
    }
function imm($items){
    
    $imm01= array(
        "V03.1"=> "Vaccine for Typhiod-Paratyphoid alone" ,
        "V03.2"=> "Vaccine for Tuberculosis" ,
        "V03.6"=> "Vaccine for Pertussis" ,
        "V03.7"=> "Vaccine for Tetanus Toxoid alone" ,
        "V03.81"=> "Vaccine for Hemophilus Influenza, Type B", 
        "V03.82"=> "Vaccine for Streptococcus Pneumoniae" ,
        "V03.9"=> "Vaccine for Single Bacterial Disease NEC", 
        "V04.0"=> "Vaccine for Poliomyelitis",
        "V04.1"=> "Vaccine for Smallpox",
        "V04.2"=> "Vaccine for Measles" ,
        "V04.3"=> "Vaccine for Rubella",
        "V04.6"=> "Vaccine for Mumps",
        "V04.8"=> "Vaccine for Influenza",
        "V05.3"=> "Vaccine for Viral Hepatitis" ,
        "V06.1"=> "Vaccine for DTP" ,
        "V06.3"=> "Vaccine for DTP + Polio" ,
        "V06.4"=> "Vaccine for Measles-Mumps-Rubella [MMR]" ,
        "V06.8"=>"Vaccine for Other Combinations" ,
        "90701"=> "DTP Immunization" ,
        "90702"=> "DT Immunization" ,
        "90703"=> "Tetanus Immunization" ,
        "90704"=> "Mumps Immunization" ,
        "90705"=> "Measles Immunization" ,
        "90706"=> "Rubella Immunization" ,
        "90707"=> "MMR Virus Immunization" ,
        "90708"=> "Measles-Rubella Immunization" ,
        "90712"=> "Oral Poliovirus Immunization" ,
        "90718"=> "TD Immunization" ,
        "90728"=> "BCG Immunization" ,
        "90744"=> "Hepatitis B Immunization",
    );
    $imm02= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
        );
    $imm04= array(
            "1"=>"First",
            "2"=>"Second", 
            "3"=>"Third",
            "4"=>"Fourth",
            "5"=>"Fifth",
            "6"=>"Sixth",
            "7"=>"Seventh",
            "8"=>"Eighth",
            "9"=>"Ninth",
            "10"=>"Medical Exemption",
            "11"=>"Personal Exemption",
            "12"=>"Religious Exemption",
            "13"=>"Had the Disease",
            "14"=>"Has Not Had the Disease",
        );
    $imm05= array(
            "CQ"=>"County Record",
            "HC"=>"Health Certificate", 
            "HR"=>"Health Clinic Records",
            "IR"=>"State School Immunization Records",
            "MG"=>"Migrant Student Records Trnasfer System Record",
            "PY"=>"Physician's Report",
        );
$itemSize = count($items); 
    for($i=$itemSize; $i<=5;$i++){
        $items[$i]="";
    }
    $immunizationCode = isset($imm01[$items[1]]) ? $imm01[$items[1]] : $items[1];
    $dateFormat = isset($imm02[$items[2]]) ? $imm02[$items[2]] : "";
    $immunizationStatus = isset($imm04[$items[4]]) ? $imm04[$items[4]] : "";
    $reportType = isset($imm05[$items[5]]) ? $imm05[$items[5]] : "";
    $ind = array("immunizationCode"=>$immunizationCode,"dateFormat"=>$dateFormat, "date"=>findDateByFormat($dateFormat,$items[3]), "immunizationStatus"=>$immunizationStatus,"reportType"=>$reportType);
    return $ind; 
}


function ses($items){
    
    $ses04= array(
            "1"=>"Full year",
            "2"=>"Semester", 
            "3"=>"Trimester",
            "4"=>"Quarter",
            "5"=>"Quinmester",
            "6"=>"Mini-term",
            "7"=>"Summer Session",
            "8"=>"Intersession (Year Round Schools)",
            "9"=>"Long Session which is longer thant a semester or quarter or trimester but shorter than a full year",
        );
    $ses06= array(
            "CM"=>"CCYYMM",
            "CY"=>"CCYY", 
            "D8"=>"CCYYMMDD",
            "DB"=>"MMDDCCYY",
            "MD"=>"MMDD",
        );
    $ses10 = array(
        "01"=>"1", 
        "02"=>"2", 
        "03"=>"3", 
        "04"=>"4", 
        "05"=>"5", 
        "06"=>"6", 
        "07"=>"7", 
        "08"=>"8",
        "09"=>"9",
        "0K"=>"Kindergarten",
        "10"=>"10",
        "11"=>"11",
        "12"=>"12",
        "20"=>"Non-Degree or Temporary Undergraduate in Postsecondary",
        "21"=>"PostSecondary First Year",
        "22"=>"PostSecondary Sophmore",
        "23"=>"PostSecondary Junior",
        "24"=>"PostSecondary Senior",
        "25"=>"Post-Bacc",
        "26"=>"Non-Degree Graduate",
        "27"=>"Professional",
        "28"=>"Masters",
        "29"=>"Doctoral",
        "30"=>"Postdoctoral",
        "31"=>"Bachelor Preliminary",
        "32"=>"Fifth year",
        "33"=>"Masters Qualifying Year",
        "AD"=>"Adult",
        "AS"=>"Associate Degree",
        "BD"=>"Baccalaureate (Bachelor's) Degree",
        "DD"=>"Doctoral Degree",
        "EL"=>"Elementary",
        "HG"=> "High School Graduate or Equivalent",
        "HS"=> "Attended high school, but did not graduate",
        "IF"=>"Infant",
        "MD"=>"Masters Degree",
        "MS"=>"Middle/Junior School",
        "P0"=>"Pre-Kindergarten L0",
        "P1"=>"Pre-Kindergarten L1",
        "P2"=>"Pre-Kindergarten L2",
        "P3"=>"Pre-Kindergarten L3",
        "P4"=>"Pre-Kindergarten L4",
        "P5"=>"Pre-Kindergarten L5",
        "PC"=> "Postsecondary Certificate or Diploma",
        "PD"=> "Professional Degree or Certification",
        "PF" =>'Professional',
        "PK"=>"Pre-Kindergarten",
        "PS"=>"Some Postsecondary",
        "SS"=>"Secondary School",
        "UN"=>"Ungraded",
        "VS"=>"Vocational School",
    );

    $ses11= array(
            "75"=>"State Assigned Number", 
            "76"=>"Local School Number", 
            "81"=>"CIP", 
            "82"=>"HEGIS", 
            "CA"=>"Canada Course Codes", 
            "CC"=>"Canada Curriculum Codes", 
        );
    $ses14 = array(
        "B35"=> "Highest Honors" ,
        "B36"=> "Second Highest Honors" ,
        "B37"=> "Third Highest Honors", 
        "B38"=> "Dropped" ,
        "B39"=> "Academic Probation" ,
        "B40"=> "Suspended" ,
        "D26"=> "Retained in Current Grade" ,
        "D27"=> "Placed in Next Grade After Expected Grade" ,
        "D28"=> "Placed in Transitional Program (K-1)" ,
        "D29"=> "Status Pending Completion of Summer School (K-12)" ,
        "D31"=> "Administratively Placed in a Higher Grade" ,
        "D32"=> "Academically Placed in a Higher Grade" ,
        "D33"=> "Promotion Status not Applicable" ,
        "D34"=> "Promoted" ,
        "EB3"=> "Withdrawn",
    );
$itemSize = count($items); 
    for($i=$itemSize; $i<=14;$i++){
        $items[$i]="";
    }

    $sessionCode = isset($ses04[$items[4]]) ? $ses04[$items[4]] : "";
    $dateFormat = isset($ses06[$items[6]]) ? $ses06[$items[6]] : "";
    $dateFormat2 = isset($ses06[$items[8]]) ? $ses06[$items[8]] : "";
    $individualLevel = isset($ses10[$items[10]]) ? $ses10[$items[10]] : "";
    $curriculumCode = isset($ses11[$items[11]]) ? $ses11[$items[11]] : "";
    $honorsCode = isset($ses14[$items[14]]) ? $ses14[$items[14]] : "";
    $ind = array("sessionStart"=>findDate($items[1]),"sessionCount"=>$items[2], "date"=>findDateByFormat("CCYY-CCYY",$items[3]), "sessionCode"=>$sessionCode, "sessionName"=>$items[5], "dateFormat"=>$dateFormat, "dateStart"=>findDateByFormat($dateFormat,$items[7]), "dateFormat2"=>$dateFormat2, "dateEnd"=>findDateByFormat($dateFormat2,$items[9]), "individualLevel"=>$individualLevel, "curriculumCode"=>$curriculumCode, "code"=>$items[12], "name"=>$items[13],"honorsCode"=>$honorsCode);
    return $ind; 
}
?>