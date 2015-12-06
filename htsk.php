
<?php
// HTSK by htmlguy - MIT Licensed
// Copyright (C) 2015 htmlguy
// Scroll Down Further for Configuration

// Start the session.
session_start();

// This class represents one single task.
class Task {
  public $proc;     // The file that stores the process ID in it.
  public $command;  // The command to be run.
  public $name;     // The name of the task.
  public $outfile;  // The file that stores the output of the command in it.
  public function __construct($name, $command, $proc, $outfile){
    $this->proc = $proc;
    $this->command = $command;
    $this->name = $name;
    $this->outfile = $outfile;
  }
  public function run(){
    // Run the command, piping output to the outfile, and printing the process id to the procfile.
    popen(sprintf("%s > %s 2>&1 & echo $! > %s", $this->command, $this->outfile, $this->proc), "r");
  }
  public function kill(){
    // Run the kill command on the process id.
    exec(sprintf("kill %s", file_get_contents($this->proc)));
  }
  public function running(){
    try {
      // Return true if the process id from the file is currently running.
      $result = shell_exec(sprintf("ps %d", file_get_contents($this->proc)));
      if (count(preg_split("/\n/", $result)) > 2){
        return true;
      }
    } catch (Exception $e){}
    return false;
  }
  public function output() {
    return file_get_contents($this->outfile);
  }
}


// >>> CONFIGURATION <<<

// Tasks List
// Repeat 'new Task(name, command, procfile, outfile)' for each task, separated with commas.
$tasks = [
    new Task("Task Title", "/path/to/script.sh --options", "/path/to/store/processid.txt", "/path/to/store/output.txt")
];
// The Auth Password
$password = "password";

// >>> END CONFIGURATION <<<


// If the user is trying to authenticate
if(isset($_POST['password'])) {
  // If the password is correct, log them in.
  if (strcmp($_POST['password'], $password) == 0) {
    $_SESSION['loggedin'] = 1;
  // If the password is incorrect, log them out.
  } else {
    $_SESSION['loggedin'] = 0;
  }
}

// If the user is logged in, obey commands to start or stop processes.
if($_SESSION['loggedin'] == 1) {
  if (isset($_GET['stop'])) {
    $tasks[$_GET['stop']]->kill();
    header("Location: ?");
  } else if (isset($_GET['start'])) {
    $tasks[$_GET['start']]->run();
    header("Location: ?");
  }
}
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>HTSK Controller</title>
    <style>
    * { box-sizing: border-box; }
    body {
      font-family: sans-serif;
      color: #222;
      margin: 0;
    }
    .meta {
      font-size: 0.9em;
      color: #fff;
      font-family: monospace;
      background: #090d54;
      padding: 0.3ex;
    }
    .btn {
      background: #222;
      color: #fff;
      text-decoration: none;
      padding: 1em;
      width: 6em;
      display: block;
      text-align: center;
    }
    .meta a {
      color: inherit;
    }
    .task {
      padding: 0.5em 5em;
      border-bottom: 1px solid #222;
    }
    .running, .stopped {
      display: inline-block;
      width: 0.7em;
      height: 0.7em;
      border-radius: 0.7em;
      float: left;
      margin-right: 0.5em;
    }
    .running {
      background: #00f;
    }
    .stopped {
      background: #f00;
    }
    .task a {
      float: right;
      text-decoration: none;
      background: #333;
      color: #fff;
      padding: 0.3ex;
      width: 5.5em;
      text-align: center;
      margin: 0 0.5em;
    }
    textarea {
      width: 80%;
      margin: 0 auto;
      display: block;
      height: 15em;
    }
    </style>
    <?php
      // Constantly reload if logged in.
      if($_SESSION['loggedin'] == 1) {
        echo "<meta http-equiv='refresh' content='5'>";
      }
    ?>
  </head>
  <body>
    <div class="meta">HTSK 1.0 by <a href="http://htmlguy.cu.cc">htmlguy</a></div>
    <?php
      // If the user is logged in, print a task list UI.
      if($_SESSION['loggedin'] == 1) {
        foreach($tasks as $index => $task) {
        ?>
          <div class="task">
            <b><?php echo $task->name; ?></b>
            <?php if ($task->running()) { ?>
              <span class="running"></span>
              <a href="?stop=<?php echo $index; ?>">STOP</a>
              <textarea><?php echo $task->output(); ?></textarea>
            <?php } else { ?>
              <span class="stopped"></span>
              <a href="?start=<?php echo $index; ?>">START</a>
            <?php } ?>
          </div>
          <form method="post">
            <input type="hidden" name="password" value="_***_">
            <input type="submit" value="Log Out">
          </form>
        <?php
        }
      // If the user is logged out, print a login screen.
      } else {
      ?>
        <h1>Log In</h1>
        <form method="post">
          <input type="password" name="password" placeholder="Auth Password">
          <input type="submit" value="Log In">
        </form>
      <?php
      }
    ?>
    <script>
      // Autoscroll all textareas to the bottom.
      var tas = document.querySelectorAll("textarea");
      for (var i = 0; i < tas.length; i++) {
        tas[i].scrollTop = tas[i].scrollHeight;
      }
    </script>
  </body>
</html>
