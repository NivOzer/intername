# Media Supreme Project

## Setup Instructions

1. download the files.
3. Update the `submit_lead.php` file with your database credentials.
4. Usename for loging in is (admin,password) (password is hashed but verified)
5. Access the landing page at `http://localhost/index.php`.
6. Access the back office login at `http://localhost/login.php`.

note - You didn't specify which site should be first , i assumed Login
but it seemed like you wanted the landing page to be index(adding a lead) 
i would suggest going first to /login

### Technologies Used
- PHP
- MySQL
- HTML
- JAVASCRIPT
- CSS

Questions:
1.What's the difference between the include() and require() functions?
  includes means even if it doesnt exist it will try to include it while require will not run if the file doesnt exist
  
2. Why would you use === instead of == ?
  === strict comparison it also checks the type and the value instead of just the value - ill use it when i want to be strict with checking a variable

4. What are the __construct() and __destruct() methods in a PHP class?
  __construct() = constructor of a class - called when object is created, __destruct() destructor , called when object is destroyed/removed

6. If you encounter error code 500 when executing a script, write the steps you would take to debug the error and solve it.
  Server logging - and checking them (most of the time they provid good understanding of the bugg
  stackoverflow/chatGPT for understing the problem
  echo testlines
  check permissions
  devide the code to minimizing my code for the bugged section step by step
  check Apache / MySQL server.

