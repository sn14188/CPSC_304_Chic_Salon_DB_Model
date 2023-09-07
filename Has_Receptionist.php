<html>
<head>
    <h1>Chic Salon</h1>
    <h2>Back to Home</h2>
    <form action="hello.php">
        <p><input type="submit" value="Home"></p>
    </form>
</head>
<body>
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

    <h2>Insert Values into Has_Receptionist Table</h2>
    <form method="POST" action="Has_Receptionist.php">
        <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
        rId: <input type="text" name="rID"> <br /><br />
        name: <input type="text" name="name"> <br /><br />
        phoneNum: <input type="text" name="phoneNum"> <br /><br />
        salonName: <input type="text" name="salonName"> <br /><br />

        <input type="submit" value="Insert" name="insertSubmit">
    </form>

    <h2>Delete Data from Receptionist Table</h2>
    <form method="POST" action="Has_Receptionist.php">
        <input type="hidden" id="deleteTupleRequest" name="deleteTupleRequest">
        rId: <input type="text" name="insDrId"> <br /><br />

        <input type="submit" value="Delete" name="deleteTuple">
    </form>

    <h2>Update Data from Receptionist Table</h2>
    <form method="POST" action="Has_Receptionist.php">
        <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
        Select rID to update: <input type="text" name="insDrId">
        </select><br /><br />
        Select Attribute to update:
        <select name = "selectedAttribute">
            <option value="name">name</option>
            <option value="phoneNum">phoneNum</option>
            <option value="salonName">salonName</option>
        </select><br /><br />
        New Value: <input type="text" name="newValue"> <br /><br />
        <input type="submit" value="Update" name="updateSubmit">
    </form>

    <h2>Display Receptionist Information</h2>
    <form method="GET" action="Has_Receptionist.php">
        <input type="hidden" id="printTableRequest" name="printTableRequest">
        <input type="submit" name="printTable" value="Print Table">
    </form>

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
        echo "$statement";
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
            echo "<tr><td>" . $row["RID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["PHONENUM"] . "</td><td>" . $row["SALONNAME"] . "</td></tr>";
        }

        echo "</table>";
    }


    function handleDeleteTupleRequest() {
        global $db_conn;

        $r_ID = $_POST['insDrId'];

        executePlainSQL("DELETE FROM Has_Receptionist WHERE rID ='" . $r_ID . "'");
        OCI_Commit($db_conn);
    }

    function handleInsertRequest() {
        global $db_conn;

        // Getting the values from the user and insert data into the table
        $tuple = array(
            ":bind1" => $_POST['rID'],
            ":bind2" => $_POST['name'],
            ":bind3" => $_POST['phoneNum'],
            ":bind4" => $_POST['salonName']
        );

        $alltuples = array($tuple);

        executeBoundSQL("INSERT INTO Has_Receptionist VALUES (:bind1, :bind2, :bind3, :bind4)", $alltuples);
        OCICommit($db_conn);
    }

    function handlePrintTableRequest() {
        global $db_conn;
        $result = executePlainSQL("SELECT * FROM Has_Receptionist");
        printResult($result);
        OCICommit($db_conn);
    }

    function handleUpdateRequest() {
        global $db_conn;
        
        $selectedRID = $_POST['insDrId'];
        $selectedAttribute = $_POST['selectedAttribute'];
        $newValue = $_POST['newValue'];

        $updateQuery = "UPDATE Has_Receptionist SET $selectedAttribute = '$newValue' WHERE rID = '$selectedRID'";
        executePlainSQL($updateQuery);
        OCICommit($db_conn);
    }

    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('insertQueryRequest', $_POST)) {
                handleInsertRequest();
                handlePrintTableRequest();
            } elseif (array_key_exists('deleteTupleRequest', $_POST)) {
                handleDeleteTupleRequest();
                handlePrintTableRequest();
            } elseif (array_key_exists('updateQueryRequest', $_POST)) {
                handleUpdateRequest();
                handlePrintTableRequest();
            }
            disconnectFromDB();
        }
    }

    // HANDLE ALL GET ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handleGETRequest() {
        if (connectToDB()) {
            if (array_key_exists('printTableRequest', $_GET)) {
                handlePrintTableRequest();
            }
            disconnectFromDB();
        } 
    }

    if (isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['deleteTuple'])) {
        handlePOSTRequest();
    } else if (isset($_GET['printTableRequest']) ) {
        handleGETRequest();
    }
    ?>

</body>
</html>
