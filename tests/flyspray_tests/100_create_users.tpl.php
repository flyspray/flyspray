<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-AU" xml:lang="en-AU">
<head>
  <title>Tests</title>
  <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
  <link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>
  <table class="mytable" border="0" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td colspan="3">Create users</td>
      </tr>
    </thead>
    <tbody>
      <?php
$this->plugin('login', 'super', 'super');
// Make a developer
$user['username'] = 'developer';
$user['password'] = 'developer';
$user['realname'] = 'Joe Developer';
$user['email'] = 'develop@example.com';
$user['jabberid'] = 'jabbo';
//      $user['notification'] = 'email';
$user['group'] = 'Developers';
$this->plugin('create_user', $user);
$this->plugin('verify_user', $user);

// Make a reporter
$user['username'] = 'reporter';
$user['password'] = 'reporter';
$user['realname'] = 'Ann Reporter';
$user['email'] = 'report@example.com';
$user['jabberid'] = 'jabbodo';
//$user['notification'] = '';
$user['group'] = 'Reporters';
$this->plugin('create_user', $user);
$this->plugin('verify_user', $user);

// Make a basic user
$user['username'] = 'basic';
$user['password'] = 'basic';
$user['realname'] = 'Barney Basic';
$user['email'] = 'basic@example.com';
$user['jabberid'] = 'jododo';
//      $user['notification'] = '';
$user['group'] = 'Basic';
$this->plugin('create_user', $user);
$this->plugin('verify_user', $user);

// Verify developer permissions 
$this->plugin('logout');
$this->plugin('login', 'developer', 'developer');
$this->plugin('verify_permissions', array ('manage project', 'view tasks', 'open new tasks', 'modify own tasks', 'modify all tasks', 'view comments', 'add comments', ' edit comments', ' delete comments', 'view attachments', 'create attachments', 'delete attachments', 'view history', 'close own tasks', 'close other tasks', 'assign to self', 'assign others to self', 'view reports', 'global view'));
$this->plugin('logout');

// Verify reporter permissions
$this->plugin('login', 'reporter', 'reporter');
$this->plugin('verify_permissions', array ('view tasks', 'open new tasks', 'view comments', 'add comments', 'view attachments', 'global view'));
$this->plugin('logout');

// Verify basic user permissions
$this->plugin('login', 'basic', 'basic');
$this->plugin('verify_permissions', array ());
$this->plugin('logout');
?>
    </tbody>
  </table>
</body>
</html>