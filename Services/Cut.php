<html>
<header>
    <h1>Chic Salon</h1>
    <h2>Back to Previous Page</h2>
    <form action="../Services.php">
        <p><input type="submit" value="Go back to Services Main Page"></p>
    </form>

    <hr>
    <h2> Cut </h2>
</header>

<h2>Display Information</h2>
    <form method="GET" action="Cut.php">
        <input type="hidden" id="printTableRequest" name="printTableRequest">
        <input type="submit" name="printTable" value="Print Table">
    </form>

    <?php
    //this tells the system that it's no longer just parsing html; it's now parsing PHP
    include '../connectToDB.php';

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
        echo "<br>Retrieved data from table Haircut:<br>";
        echo "<table>";
        echo "<tr><th>serviceID</th><th>cutStyle</th></tr>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["SERVICEID"] . "</td><td>" . $row["CUTSTYLE"] . "</td></tr>";
        }
        echo "</table>";
    }



    function handlePrintTableRequest($tableName)
    {
        global $db_conn;

        $result = executePlainSQL("SELECT * FROM $tableName");
        printResult($result);
        OCICommit($db_conn);
    }

    function handleInsertRequest()
    {
        global $db_conn;

        // Getting the values from the user and insert data into the table
        $tuple = array(
            ":bind1" => $_POST['serviceID'],
            ":bind2" => $_POST['cutStyle'],
            ":bind3" => $_POST['price'],
            ":bind4" => $_POST['duration']
        );

        $alltuples = array($tuple);

        executeBoundSQL("INSERT INTO Service VALUES (:bind1, :bind3, :bind4)", $alltuples);
        executeBoundSQL("INSERT INTO Haircut VALUES (:bind1, :bind2)", $alltuples);

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
            } elseif (array_key_exists('deleteCascadeTupleRequest', $_POST)) {
                handleDeleteCascadeTupleRequest();
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
    function handleGETRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('countTuples', $_GET)) {
                // handleCountRequest();
            } elseif (array_key_exists('printTableRequest', $_GET)) {
                handlePrintTableRequest('Haircut');

            }
            disconnectFromDB();
        }
    }

    if (isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['deleteTuple'])) {
        handlePOSTRequest();
    } else if (isset($_GET['printTableRequest'])) {
        handleGETRequest();
    }
    ?>

<hr>
    <h2>Managing Haircuts</h2>
    <h3>Insert Haircut Values - price & duration will respectively be added to Service table</h3>
    <form method="POST" action="Cut.php">
        <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
        serviceID: <input type="text" name="serviceID"> <br /><br />
        cutStyle: <input type="text" name="cutStyle"> <br /><br />
        price: <input type="text" name="price"> <br /><br />
        duration: <input type="text" name="duration"> <br /><br />
        <input type="submit" value="Insert" name="insertSubmit">
    </form>


    
</body>
</html>