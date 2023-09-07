<html>
<header>
    <h1>Chic Salon</h1>
    <h2>Back to Previous Page</h2>
    <form action="FeedbackAndPayments.php">
        <p><input type="submit" value="Go back to Feedback & Payment"></p>
    </form>

    <hr>
    <h2> Payment and Sales Report </h2>
</header>

    <h2>Display Information</h2>
    <form method="GET" action="Payment.php">
        <input type="hidden" id="printTableRequest" name="printTableRequest">
        <input type="submit" name="printTable" value="Print Table">
    </form>

    <?php
    //this tells the system that it's no longer just parsing html; it's now parsing PHP
    include 'connectToDB.php';

    $success = True; //keep track of errors so it redirects the page only if there are no errors
    $db_conn = NULL; // edit the login credentials in connectToDB()
    $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

    function debugAlertMessage($message) {
        global $show_debug_alert_messages;

        if ($show_debug_alert_messages) {
            echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

    function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
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
            
    function executeBoundSQL($cmdstr, $list) {
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
            
    // For Payment1 table
    function printResult_1($result) {
        echo "<br>Retrieved data from table Payment1:<br>";
        echo "<table>";
        echo "<tr><th>transactionNum</th><th>method</th><th>baseAmount</th><th>tipAmount</th><th>rID</th><th>cID</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["TRANSACTIONNUM"] . "</td><td>" . $row["METHOD"] . "</td><td>" . $row["BASEAMOUNT"] . "</td><td>" . $row["TIPAMOUNT"] . "</td><td>" . $row["RID"] . "</td><td>" . $row["CID"] . "</td></tr>";
        }
        echo "</table>";
    }

    // For Payment2 table
    function printResult_2($result) {
        echo "<br>Retrieved data from table Payment2:<br>";
        echo "<table>";
        echo "<tr><th>baseAmount</th><th>tipAmount</th><th>totalAmount</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["BASEAMOUNT"] . "</td><td>" . $row["TIPAMOUNT"] . "</td><td>" . $row["TOTALAMOUNT"] . "</td><tr>";
        }
        echo "</table>";
    }

    function handleDeleteTupleRequest() {
        global $db_conn;

        $r_ID = $_POST['insDrId'];

        executePlainSQL("DELETE FROM Payment1 WHERE transactionNum ='" . $r_ID . "'");
        OCI_Commit($db_conn);
    }

    function handleInsertRequest() {
        global $db_conn;

        // Getting the values from the user and insert data into the table
        $tuple = array(
            ":bind1" => $_POST['transactionNum'],
            ":bind2" => $_POST['method'],
            ":bind3" => $_POST['baseAmount'],
            ":bind4" => $_POST['tipAmount'],
            ":bind5" => $_POST['rID'],
            ":bind6" => $_POST['cID']
        );

        $alltuples = array($tuple);
        executeBoundSQL("INSERT INTO Payment1 VALUES (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6)", $alltuples);
        OCICommit($db_conn);
    }

    function handlePrintTableRequest($tableName) {
        global $db_conn;

        $result = executePlainSQL("SELECT * FROM $tableName");

        if ($tableName == 'Payment1') {
            printResult_1($result);
        } elseif ($tableName == 'Payment2') {
            printResult_2($result);
        }
        
        OCICommit($db_conn);
    }

    // Next two functions for Aggregation with Group By
    function printResultGroupBy($result) {
        echo "<br>Grouped by Payment Method; Payment1:<br>";
        echo "<table>";
        echo "<tr><th>method</th><th>totalBaseAmount</th><th>totalTipAmount</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["METHOD"] . "</td><td>" . $row["TOTALBASEAMOUNT"] . "</td><td>" . $row["TOTALTIPAMOUNT"] . "</td><tr>";
        }
        echo "</table>";
    }

    function handlePrintTableRequestGroupBy($method) {
        global $db_conn;
        $result = executePlainSQL("SELECT $method, SUM(baseAmount) as totalBaseAmount, SUM(tipAmount) as totalTipAmount FROM Payment1 GROUP BY $method");

        printResultGroupBy($result);
        OCICommit($db_conn);
    }

    // Next two functions for Aggregation with Group By and Having
    function printResultGroupByAndHaving($result) {
        echo "<br>Grouped by Payment Method with Total Base Amount > 200:<br>";
        echo "<table>";
        echo "<tr><th>method</th><th>totalBaseAmount</th><th>totalTipAmount</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["METHOD"] . "</td><td>" . $row["TOTALBASEAMOUNT"] . "</td><td>" . $row["TOTALTIPAMOUNT"] . "</td><tr>";
        }
        echo "</table>";
    }

    function handlePrintTableRequestGroupByAndHaving($method) {
        global $db_conn;
        $result = executePlainSQL("SELECT $method, SUM(baseAmount) as totalBaseAmount, SUM(tipAmount) as totalTipAmount FROM Payment1 GROUP BY $method HAVING SUM(baseAmount) > 200");

        printResultGroupByAndHaving($result);
        OCICommit($db_conn);
    }
            
            
    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest() {
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
                handlePrintTableRequest('Payment1');
                handlePrintTableRequest('Payment2');
                handlePrintTableRequestGroupBy('method');
                handlePrintTableRequestGroupByAndHaving('method');
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
    <h2>Managing Payments</h2>
    <h3>Insert Payment Values</h3>
    <form method="POST" action="Payment.php">
        <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
        transactionNum: <input type="text" name="transactionNum"> <br /><br />
        method: <input type="text" name="method"> <br /><br />
        baseAmount: <input type="text" name="baseAmount"> <br /><br />
        tipAmount: <input type="text" name="tipAmount"> <br /><br />
        rID: <input type="text" name="rID"> <br /><br />
        cID: <input type="text" name="cID"> <br /><br />
        <input type="submit" value="Insert" name="insertSubmit">
    </form>

    <h3>Delete Data from Payment Table</h3>
    <form method="POST" action="Payment.php">
        <input type="hidden" id="deleteTupleRequest" name="deleteTupleRequest">
        transactionNum: <input type="text" name="insDrId"> <br /><br />
        <input type="submit" value="Delete" name="deleteTuple">
    </form>

</body>
</html>