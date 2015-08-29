#Drivers
<table>
    <tr>
      <th>driver name</th>
      <th>details</th>
    </tr>
    <tr>
      <td>debug</td>
      <td>Does no locking, useful for dev mode</td>
    </tr>
    <tr>
      <td>file</td>
      <td>uses <a href="http://php.net/manual/en/function.flock.php">flock</a><br>If intended to use in a production environment, make sure the path you use is shared across all your servers (ie. network mount)</td>
    </tr>
    <tr>
      <td>mysql</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>memcache</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>redis</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>mongodb</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>mariadb</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>postgresql</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>mssql</td>
      <td>Not implemented yet</td>
    </tr>
</table>