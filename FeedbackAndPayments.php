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

        <h2> Feedback and Payments </h2>
    </header>

    <h2>Choose Table to Interact With</h2>
    <form method="POST" id="tableSelectForm">
        <select name="selectedTable" id="selectedTable">
            <option value="Feedback">Feedback</option>
            <option value="Payment">Payment</option>
        </select>
        <input type="submit" value="Select Table">
    </form>

    <!-- <h2>Display Information</h2>
    <form method="GET" action="Has_Receptionist.php">
        <input type="hidden" id="printTableRequest" name="printTableRequest">
        <input type="submit" name="printTable" value="Print Table">
    </form> -->

    <script>
        document.getElementById('tableSelectForm').addEventListener('submit', function (event) {
            event.preventDefault();
            // Get the selected option from the drop-down menu
            const selectedTable = document.getElementById('selectedTable').value;

            // Redirect the user to the corresponding PHP page based on the selected option
            if (selectedTable === 'Feedback') {
                window.location.href = 'Feedback.php';
            } else if (selectedTable === 'Payment') {
                window.location.href = 'Payment.php';
            }
        });
    </script>





    <?php
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


    function handlePOSTRequest()
    {
        if (connectToDB()) {
            disconnectFromDB();
        }
    }

    function handleGETRequest()
    {
        if (connectToDB()) {
            disconnectFromDB();
        }
    }



    if (isset($_GET['findAppointments']) ) {
      //  echo "loading data";
      //  loadData();
        handleGETRequest();
    }

    
    ?>
</body>
</html>
