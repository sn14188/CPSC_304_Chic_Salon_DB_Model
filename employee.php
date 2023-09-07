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

  <head>
    <title>Chic Salon</title>
</head>
<body>
    <h1>Chic Salon</h1>
    <h2>Back to Home</h2>
    <form action="hello.php">
        <p><input type="submit" value="Home"></p>
    </form>

    <h2>Choose Table to Interact With</h2>
    <form method="POST" id="tableSelectForm">
        <select name="selectedTable" id="selectedTable">
            <option value="Has_Receptionist">Has_Receptionist</option>
            <option value="Assist_StylistAssistant">Assist_StylistAssistant</option>
            <option value="HasMany_Stylist">HasMany_Stylist</option>
        </select>
        <input type="submit" value="Select Table">
    </form>

    <script>
        document.getElementById('tableSelectForm').addEventListener('submit', function (event) {
            event.preventDefault();
            // Get the selected option from the drop-down menu
            const selectedTable = document.getElementById('selectedTable').value;

            // Redirect the user to the corresponding PHP page based on the selected option
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

    function printResult($result)
    {
        echo "<br>Retrieved data from table Has_Receptionist:<br>";
        echo "<table>";
        echo "<tr><th>rID</th><th>Name</th><th>phone_num</th><th>salonName</th></tr>";

        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["RID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["PHONE_NUM"] . "</td><td>" . $row["SALONNAME"] . "</td></tr>";
        }

        echo "</table>";
    }

    function printAssistantResult($result)
    {
        echo "<br>Retrieved data from table Assist_StylistAssistant:<br>";
        echo "<table>";
        echo "<tr><th>saID</th><th>Name</th><th>phone_num</th><th>sID</th></tr>";

        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["SAID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["PHONE_NUM"] . "</td><td>" . $row["SID"] . "</td></tr>";
        }

        echo "</table>";
    }



    function handleDeleteTupleRequest()
    {
        global $db_conn;

        $r_ID = $_POST['insDrId'];

        executePlainSQL("DELETE FROM Has_Receptionist WHERE rID ='" . $r_ID . "'");
        OCI_Commit($db_conn);
    }

    function handleDeleteAssistantRequest()
    {
        global $db_conn;

        $saID = $_POST['delSaID'];
        $sID = $_POST['delSID'];

        executePlainSQL("DELETE FROM Assist_StylistAssistant WHERE saID ='" . $saID . "' AND sID ='" . $sID . "'");
        OCI_Commit($db_conn);
    }

    function handleInsertRequest()
    {
        global $db_conn;

        // Getting the values from the user and insert data into the table
        $tuple = array(
            ":bind1" => $_POST['rID'],
            ":bind2" => $_POST['name'],
            ":bind3" => $_POST['phone_num'],
            ":bind4" => $_POST['salonName']
        );

        $alltuples = array($tuple);

        executeBoundSQL("INSERT INTO Has_Receptionist VALUES (:bind1, :bind2, :bind3, :bind4)", $alltuples);
        OCICommit($db_conn);
    }

    function handleInsertAssistantRequest()
    {
        global $db_conn;

        // Getting the values from the user and insert data into the table
        $tuple = array(
            ":bind1" => $_POST['saID'],
            ":bind2" => $_POST['assistantName'],
            ":bind3" => $_POST['assistantPhone'],
            ":bind4" => $_POST['sID']
        );

        $alltuples = array($tuple);

        executeBoundSQL("INSERT INTO Assist_StylistAssistant VALUES (:bind1, :bind2, :bind3, :bind4)", $alltuples);
        OCICommit($db_conn);
    }

    function handlePrintTableRequest()
    {
        global $db_conn;

        $result = executePlainSQL("SELECT * FROM Has_Receptionist");

        printResult($result);
        OCICommit($db_conn);
    }

    function handlePrintAssistantTableRequest()
    {
        global $db_conn;

        $result = executePlainSQL("SELECT * FROM Assist_StylistAssistant");

        printAssistantResult($result);
        OCICommit($db_conn);
    }

    function loadEmployeeData()
    {
        global $db_conn;

        // Open and read the "employee.sql" file
        $sql_file = "employee.sql";
        $sql_contents = file_get_contents($sql_file);

        // Split the contents into individual commands
        $sql_commands = explode(";", $sql_contents);

        // Execute each command
        foreach ($sql_commands as $cmd) {
            $cmd = trim($cmd);
            if (!empty($cmd)) {
                executePlainSQL($cmd);
            }
        }

        OCICommit($db_conn);
    }




    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('insertQueryRequest', $_POST)) {
               loadEmployeeData();
             // how to load when page is first loaded
                handleInsertRequest();
                handlePrintTableRequest();
            } elseif (array_key_exists('deleteTupleRequest', $_POST)) {
                handleDeleteTupleRequest();
                handlePrintTableRequest();
            } elseif (array_key_exists('insertAssistantRequest', $_POST)) {
             //   loadEmployeeData();
                handleInsertAssistantRequest();
                handlePrintAssistantTableRequest();
            } elseif (array_key_exists('deleteAssistantRequest', $_POST)) {
                handleDeleteAssistantRequest();
                handlePrintAssistantTableRequest();
            }
            disconnectFromDB();
        }
    }

    // HANDLE ALL GET ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handleGETRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('countTuples', $_GET)) {
                handleCountRequest();
            } elseif (array_key_exists('printTableRequest', $_GET)) {
                handlePrintTableRequest();
            } elseif (array_key_exists('printAssistantTableRequest', $_GET)) {
                handlePrintAssistantTableRequest();
            }

            disconnectFromDB();
        }
    }

    if (isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['deleteTuple']) || isset($_POST['insertAssistantSubmit']) || isset($_POST['deleteAssistantSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['printTableRequest']) || isset($_GET['printAssistantTableRequest']) ) {
        handleGETRequest();
    }
    ?>
</body>
</html>
