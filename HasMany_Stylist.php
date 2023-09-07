<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values

  IF YOU HAVE A TABLE CALLED "employeeTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the
  OCILogon below to be your ORACLE username and password -->

  <html>
<header>
    <h1>Chic Salon</h1>
    <h2>Back to Home</h2>
    <form action="hello.php">
        <p><input type="submit" value="Home"></p>
    </form>

    <h2>Choose Table to Interact With</h2>
    <form method="POST" id="tableSelectForm">
        <select name="selectedTable" id="selectedTable">
            <option value="HasMany_Stylist">HasMany_Stylist</option>
            <option value="Has_Receptionist">Has_Receptionist</option>
            <option value="Assist_StylistAssistant">Assist_StylistAssistant</option>
        </select>
        <input type="submit" value="Select Table">
    </form>

    <h1>Stylist Selection</h1>

    <form method="GET" action="HasMany_Stylist.php">
        <label for="selectedAttributes">Select Attributes To Display:</label>
        <input type="checkbox" name="selectedAttributes[]" value="sID">sID
        <input type="checkbox" name="selectedAttributes[]" value="name">Name
        <input type="checkbox" name="selectedAttributes[]" value="phoneNum">Phone Number
        <input type="checkbox" name="selectedAttributes[]" value="baseSalary">Base Salary
        <input type="checkbox" name="selectedAttributes[]" value="bonus">Bonus
        <br><br>

        <label for="conditionField">Select Condition Field:</label>
        <select name="conditionField">
            <option value="sID">sID</option>
            <option value="name">Name</option>
            <option value="phoneNum">Phone Number</option>
            <option value="baseSalary">Base Salary</option>
            <option value="bonus">Bonus Salary</option>
        </select>
        <br><br>

        <label for="conditionOperator">Select Condition Operator:</label>
        <select name="conditionOperator">
            <option value="=">=</option>
            <option value=">">></option>
            <option value="<"><</option>
        </select>
        <br><br>

        <label for="conditionValue">Enter Condition Value:</label>
        <input type="text" name="conditionValue">
        <br><br>

        <input type="submit" value="Submit">
    </form>

    <script>
        document.getElementById('tableSelectForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const selectedTable = document.getElementById('selectedTable').value;

            if (selectedTable === 'Has_Receptionist') {
                window.location.href = 'Has_Receptionist.php';
            } else if (selectedTable === 'Assist_StylistAssistant') {
                window.location.href = 'Assist_StylistAssistant.php';
            } else if (selectedTable === 'HasMany_Stylist') {
                window.location.href = 'HasMany_Stylist.php';
            }
        });
    </script>
</body>

    

    <?php
    //this tells the system that it's no longer just parsing html; it's now parsing PHP
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

  

    function displayResults($result) {
        echo "<table>";
        $firstRow = true;
    
        while ($row = oci_fetch_assoc($result)) {
            if ($firstRow) {
                echo "<tr>";
                foreach ($row as $columnName => $value) {
                    echo "<th>$columnName</th>";
                }
                echo "</tr>";
                $firstRow = false;
            }
    
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
    
        echo "</table>";
    }

    // HANDLE ALL GET ROUTES
   function handleGETRequest()
    {
        if (connectToDB()) {
            $selectedAttributes = $_GET['selectedAttributes'];
            $conditionField = $_GET['conditionField'];
            $conditionOperator = $_GET['conditionOperator'];
            $conditionValue = $_GET['conditionValue'];
    
            // Construct the SELECT query
            $selectedColumns = implode(', ', $selectedAttributes);
            $sql = "SELECT $selectedColumns 
            FROM HasMany_Stylist1
            WHERE $conditionField $conditionOperator '$conditionValue'";

        
            $result = executePlainSQL($sql);
    
            displayResults($result);
    
            disconnectFromDB();
        }
    }

        handleGETRequest();
    
    ?>
</body>
</html>
