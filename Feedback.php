<html>
<header>
    <h1>Chic Salon</h1>
    <h2>Back to Previous Page</h2>
    <form action="FeedbackAndPayments.php">
        <p><input type="submit" value="Go back to Feedback & Payment"></p>
    </form>

    <hr>
    <h2> Feedback and Payments </h2>
</header>

    <h2>Display Information</h2>
    <form method="GET" action="Feedback.php">
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

    function printResult_1($result) {
        echo "<br>Retrieved data from table Feedback1:<br>";
        echo "<table>";
        echo "<tr><th>feedbackNum</th><th>rate</th><th>rID</th><th>cID</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["FEEDBACKNUM"] . "</td><td>" . $row["RATE"] . "</td><td>" . $row["RID"] . "</td><td>" . $row["CID"] . "</td></tr>";
        }
        echo "</table>";
    }

    function printResult_2($result) {
        echo "<br>Retrieved data from table Feedback2:<br>";
        echo "<table>";
        echo "<tr><th>rate</th><th>sentiment</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["RATE"] . "</td><td>" . $row["SENTIMENT"] . "</td><tr>";
        }
        echo "</table>";
    }

    function handleDeleteTupleRequest() {
        global $db_conn;

        $r_ID = $_POST['insDrId'];

        executePlainSQL("DELETE FROM Feedback1 WHERE feedbackNum ='" . $r_ID . "'");
        OCI_Commit($db_conn);
    }

    function handleInsertRequest() {
        global $db_conn;

        // Getting the values from the user and insert data into the table
        $tuple = array(
            ":bind1" => $_POST['feedbackNum'],
            ":bind2" => $_POST['rate'],
            ":bind3" => $_POST['rID'],
            ":bind4" => $_POST['cID']
        );

        $alltuples = array($tuple);

        executeBoundSQL("INSERT INTO Feedback1 VALUES (:bind1, :bind2, :bind3, :bind4)", $alltuples);
        OCICommit($db_conn);
    }

    function handlePrintTableRequest($tableName) {
        global $db_conn;

        $result = executePlainSQL("SELECT * FROM $tableName");

        if ($tableName == 'Feedback1') {
            printResult_1($result);
        } elseif ($tableName == 'Feedback2') {
            printResult_2($result);
        }
        
        OCICommit($db_conn);
    }

    // Next two functions for Nested Aggregation with Group By
    function printResultNestedGroupBy($result) {
        echo "<br>Retrieved AVG rate information of Receptionist 'Oliver James' using Nested Aggregation with Group By: <br>";
        echo "<table>";
        echo "<tr><th>receptionistID</th><th>averageRate</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["RECEPTIONISTID"] . "</td><td>" . $row["AVERAGERATE"] . "</td></tr>";
        }
        echo "</table>";
    }

    function handlePrintTableRequestNestedGroupBy($name) {
        global $db_conn;
        $result = executePlainSQL("SELECT f1.rID as RECEPTIONISTID, AVG(f1.rate) as AVERAGERATE FROM Feedback1 f1 WHERE f1.rID IN (SELECT hr.rID FROM Has_Receptionist hr WHERE hr.name = '$name') GROUP BY f1.rID");
        printResultNestedGroupBy($result);
        OCICommit($db_conn);
    }

    // Next two functions for Division
    function printResultCustomerWithoutFeedback($result) {
        echo "<br>Customers who have not provided feedback: <br>";
        echo "<table>";
        echo "<tr><th>customerID</th><th>name</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["CID"] . "</td><td>" . $row["NAME"] . "</td></tr>";
        }
        echo "</table>";
    }

    function handlePrintTableRequestDivision() { // Finds customers without feedback
        global $db_conn;
        $result = executePlainSQL("SELECT c.cID as CID, c.name as NAME FROM Customer c WHERE NOT EXISTS (SELECT * FROM Feedback1 f1 WHERE f1.cID = c.cID)");
        printResultCustomerWithoutFeedback($result);
        OCICommit($db_conn);
    }


    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('insertQueryRequest', $_POST)) {
             // loadEmployeeData();
             // how to load when page is first loaded
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
            if (array_key_exists('countTuples', $_GET)) {
                // handleCountRequest();
            } elseif (array_key_exists('printTableRequest', $_GET)) {
                handlePrintTableRequest('Feedback1');
                handlePrintTableRequest('Feedback2');
                handlePrintTableRequestNestedGroupBy('Oliver James');
                handlePrintTableRequestDivision();
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

    <hr>
    <h2>Managing Feedbacks</h2>
    <h3>Insert Feedback1 Values</h3>
    <form method="POST" action="Feedback.php">
        <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
        feedbackNum: <input type="text" name="feedbackNum"> <br /><br />
        rate: <input type="text" name="rate"> <br /><br />
        rID: <input type="text" name="rID"> <br /><br />
        cID: <input type="text" name="cID"> <br /><br />
        <input type="submit" value="Insert" name="insertSubmit">
    </form>

    <h3>Delete Data from Feedback1 Table</h3>
    <form method="POST" action="Feedback.php">
        <input type="hidden" id="deleteTupleRequest" name="deleteTupleRequest">
        feedbackNum: <input type="text" name="insDrId"> <br /><br />
        <input type="submit" value="Delete" name="deleteTuple">
    </form>

</body>
</html>
