<!DOCTYPE html>
<html>
<head>
    <title>Chic Salon</title>
</head>
<body>
    <header>
        <h1>Chic Salon</h1>
        <h2>Back to Home</h2>
        <form action="hello.php">
            <p><input type="submit" value="Home"></p>
        </form>

     <section id="findAppointments">
        <h2>Find Appointments</h2>
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="appointmentDate">Enter Appointment Date (YYMMDD):</label>
            <input type="text" name="appointmentDate" required>
            <label for="attributes">Select attributes to project:</label>
            <br>
            <input type="checkbox" name="attribute[]" value="cID">cID
            <input type="checkbox" name="attribute[]" value="name">Name
            <input type="checkbox" name="attribute[]" value="gender">Gender
            <input type="checkbox" name="attribute[]" value="phoneNum">Phone Number
            <input type="checkbox" name="attribute[]" value="lastVisit">Last Visit
            <br>
            <input type="submit" name="findAppointments" value="Find Appointments">
        </form>
        
     <?php
     session_start();
    include 'connectToDB.php';

    $success = True; //keep track of errors so it redirects the page only if there are no errors
    $db_conn = NULL; // edit the login credentials in connectToDB()
    $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

    function debugAlertMessage($message)
    {
        global $show_debug_alert_messages;

        if ($show_debug_alert_messages) {
            echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

    function executePlainSQL($cmdstr)
    { //takes a plain (no bound variables) SQL command and executes it
        //echo "<br>running ".$cmdstr."<br>";
        global $db_conn, $success;

        $statement = OCIParse($db_conn, $cmdstr);
        //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
            echo htmlentities($e['message']);
            $success = False;
        }

        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
            echo htmlentities($e['message']);
            $success = False;
        }

        // echo "$statement";
        return $statement;
    }

    function executeBoundSQL($cmdstr, $list)
    {
        /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
    In this case you don't need to create the statement several times. Bound variables cause a statement to only be
    parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
    See the sample code below for how this function is used */

        global $db_conn, $success;
        $statement = OCIParse($db_conn, $cmdstr);

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn);
            echo htmlentities($e['message']);
            $success = False;
        }

        foreach ($list as $tuple) {
            foreach ($tuple as $bind => $val) {
                //echo $val;
                //echo "<br>".$bind."<br>";
                OCIBindByName($statement, $bind, $val);
                unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                echo htmlentities($e['message']);
                echo "<br>";
                $success = False;
            }
        }

        OCICommit($db_conn);
    }

    function printResult($result)
{
    echo "<br>Retrieved data from table Appointment:<br>";
    echo "<table>";
    echo "<tr><th>Confirmation Number</th><th>Date</th><th>Time</th><th>Receptionist ID</th><th>Customer ID</th></tr>";

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        echo "<tr>";
        echo "<td>" . $row["CONFIRMATIONNUM"] . "</td>";
        echo "<td>" . $row["A_DATE"] . "</td>";
        echo "<td>" . $row["A_TIME"] . "</td>";
        echo "<td>" . $row["RID"] . "</td>";
        echo "<td>" . $row["CID"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}

function printResult2($result2)
{
  //  echo"printing result";
    echo "<table>";
    echo "<tr><th>cID</th><th>name</th><th>gender</th><th>phoneNum</th><th>lastVisit</th></tr>";


    while ($row = oci_fetch_assoc($result2)) {
      //  echo"$result2";
        echo "<tr>";
        echo "<td>" . $row["CUSTOMER_CID"] . "</td>";
        echo "<td>" . $row["CUSTOMER_NAME"] . "</td>";
        echo "<td>" . $row["CUSTOMER_GENDER"] . "</td>";
        echo "<td>" . $row["CUSTOMER_PHONENUM"] . "</td>";
        echo "<td>" . $row["CUSTOMER_LASTVISIT"] . "</td>";
       
        echo "</tr>";
    }

    echo "</table>";
}


function handleGETRequest()
    {
        global $db_conn;
        global $result2;
        if (connectToDB()) {
            if (isset($_GET['findAppointments'])) {
              $appointmentDate = $_GET['appointmentDate'];
                if (strlen($appointmentDate) === 6 && is_numeric($appointmentDate)) {
                
                    $sql = "SELECT *
                            FROM Appointment a
                            WHERE a_date = $appointmentDate";

        $selectedAttributes = $_GET['attribute'];

        $projectionAttributes = implode(', ', $selectedAttributes);

        // foreach ($selectedAttributes as $attribute) {
        //     echo "printing attribute///";
        //     echo  "c." . $attribute . "\n";
        // }

        // foreach ($selectedAttributes as $attribute) {
        //     $modifiedAttributes[] = "c." . $attribute;
        // }
        
        // $commaSeparatedAttributes = implode(', ', $modifiedAttributes);
        // echo $commaSeparatedAttributes;

        foreach ($selectedAttributes as $attribute) {
            $modifiedAttribute = "c." . $attribute;
            $alias = "customer_" . $attribute;
            $modifiedAttributesWithAlias[] = "$modifiedAttribute AS $alias";
        }
        
        $commaSeparatedAttributes = implode(', ', $modifiedAttributesWithAlias);
    
                    $sql2 = "SELECT  a.*, $commaSeparatedAttributes
                              FROM Appointment a, Customer c
                               WHERE a.cID = c.cID AND a.a_date = $appointmentDate";

            
                $result = executePlainSQL($sql);
                    $result2 = executePlainSQL($sql2);

                    printResult($result);
                    printResult2($result2);
                    printResultWithAttributes($result2,$selectedAttributes);

                } else {
                    echo "<p>Please enter a valid date in the format YYMMDD.</p>";
                }
                } 
           
        }
        OCICommit($db_conn);
        disconnectFromDB();
    }



    if (isset($_GET['findAppointments'])) {
        handleGETRequest();
    }

    
    ?>
      
    </header>


</body>
</html>
