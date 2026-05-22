<?php
$SCHOOLS=['SECA'=>'SECA - School of Engineering, Computing, and Arts','SASE'=>'SASE - School of Arts, Science, and Education','SBMA'=>'SBMA - School of Business, Management, and Accountancy','SHS'=>'SHS - Senior High School'];
$PROGRAMS_BY_SCHOOL=['SECA'=>['BSIT','BSCS','BSCompEngr','BSCivilEng','BSArchi'],'SASE'=>['Psychology','Education'],'SBMA'=>['Accountancy','Tourism'],'SHS'=>['ABM','STEM','HUMSS']];
function school_options_html($selected=''){global $SCHOOLS;$h='<option value="">Select School</option>';foreach($SCHOOLS as $c=>$l){$s=$selected===$c?'selected':'';$h.='<option value="'.e($c).'" '.$s.'>'.e($l).'</option>'; } return $h;}
function program_options_html($selected=''){global $PROGRAMS_BY_SCHOOL;$h='<option value="">Select Program</option>';foreach($PROGRAMS_BY_SCHOOL as $school=>$programs){$h.='<optgroup label="'.e($school).'">';foreach($programs as $p){$s=$selected===$p?'selected':'';$h.='<option value="'.e($p).'" '.$s.'>'.e($p).'</option>';}$h.='</optgroup>';}return $h;}
function allowed_schools(){global $SCHOOLS;return array_keys($SCHOOLS);} function allowed_programs(){global $PROGRAMS_BY_SCHOOL;$a=[];foreach($PROGRAMS_BY_SCHOOL as $ps){foreach($ps as $p)$a[]=$p;}return $a;} function school_for_program($program){global $PROGRAMS_BY_SCHOOL;foreach($PROGRAMS_BY_SCHOOL as $s=>$ps){if(in_array($program,$ps,true))return $s;}return '';}

function school_card_class($program) {
    $map = [
        'BSIT'=>'seca','BSCS'=>'seca','BSCompEngr'=>'seca','BSCivilEng'=>'seca','BSArchi'=>'seca',
        'Psychology'=>'sase','Education'=>'sase',
        'Accountancy'=>'sbma','Tourism'=>'sbma',
        'ABM'=>'shs','STEM'=>'shs','HUMSS'=>'shs',
    ];
    $s = $map[$program] ?? '';
    return $s ? 'school-' . $s : '';
}
?>