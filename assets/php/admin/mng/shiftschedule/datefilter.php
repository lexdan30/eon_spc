<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));

    $arr_id = array();

    if($param->type==2){
        $idunits = getTblUnits($con, $param->accountid);
        foreach( $idunits AS $value ){
            $dept = $value;
            $ids='0';
            //Get Managers Under person
            if( !empty( $dept ) ){
                $arr_id = array();
                $arr 	= getHierarchy($con,$dept);
                if( !empty( $arr["nodechild"] ) ){     
                    $ids = join(',', flatten($arr['nodechild']));
                    Log::v('$idunits = getTblUnits'.$ids);
                } else {
                    $ids = '0';
                }
            }
            
    
        }
    }else if($param->type==1){
        $dept = getIdUnit($con,$param->accountid);
        $ids='0';
        //Get Managers Under person
        if( !empty( $dept ) ){
            $arr_id = array();
            $arr 	= getHierarchy($con,$dept);
            if( !empty( $arr["nodechild"] ) ){     
                $ids = join(',', flatten($arr['nodechild']));
            } else {
                $ids = '0';
            }
        }
        
    }

    $Qry=new Query();
    $Qry->table="(SELECT work_date FROM vw_datacurrentworkdates2) AS a";
    $Qry->selected="DISTINCT a.work_date";
    $Qry->fields="(a.work_date BETWEEN '".$param->datefrom."' AND '".$param->dateto."') ORDER BY CONCAT(a.work_date) ASC";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        $array1 = array();
        $array2 = array();
        while($row=mysqli_fetch_assoc($rs)){

            //array_push($array1, $row['work_date']);
            
            $array1[] = array(
                'work_date' => $row['work_date'],
                
            );

        }
        
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

    $data = array(
        'work_date' => $array1,
        'employee'  => getQuery($con, $param, $ids)
    );
    $return = json_encode($data);    
print $return;
mysqli_close($con);

function getQuery($con, $param, $ids){
    $data = array();

    if($param->type==1){
        $Qry=new Query();
        $Qry->table="tbldutyroster";
        $Qry->selected="idacct";
        //$Qry->fields="unit='".$param->unit."' AND idstat=0 AND DATE BETWEEN '".$param->datefrom."' AND '".$param->dateto."' AND (secretary!=0 OR secretary is null) GROUP BY idacct";
        $Qry->fields="unit IN (".$ids.") AND (manager!=1 OR manager is null) AND idstat=0 AND DATE BETWEEN '".$param->datefrom."' AND '".$param->dateto."' AND (secretary!=0 OR secretary is null) GROUP BY idacct";
        $rs=$Qry->exe_SELECT($con);
        Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getQuery:if');
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_assoc($rs)){
                $data[] = array(
                    "idacct"=> $row['idacct']
                );
            }
        }
    }else if($param->type==2){
        $Qry=new Query();
        $Qry->table="tbldutyroster";
        $Qry->selected="idacct";
        //$Qry->fields="unit='".$param->unit."' AND idstat=0 AND DATE BETWEEN '".$param->datefrom."' AND '".$param->dateto."' AND type_creator=2 GROUP BY idacct";
        $Qry->fields="unit IN (".$ids.") AND (manager!=1 OR manager is null) AND idstat=0 AND DATE BETWEEN '".$param->datefrom."' AND '".$param->dateto."' AND type_creator=2 GROUP BY idacct";
        $rs=$Qry->exe_SELECT($con);
        Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getQuery:elseif');
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_assoc($rs)){
                $data[] = array(
                    "idacct"=> $row['idacct']
                );
            }
        }
    }

    return $data;
}

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}

function getTblUnits($con, $idacct){
    $data = array();
    $Qry=new Query();
    $Qry->table="tblbunits";
    $Qry->selected="id";
    $Qry->fields="scheduler='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTblUnits');
    if(mysqli_num_rows($rs)>=1){
        array_push($data, mysqli_fetch_assoc($rs)['id']);
    }
    return $data;
}

?>