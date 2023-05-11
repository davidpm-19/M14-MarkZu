package com.example.theVault;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.widget.TextView;
import android.widget.Toast;

import com.google.android.material.button.MaterialButton;

import java.sql.*;




public class MainActivity2 extends AppCompatActivity {

    public class DatabaseUpdateTask extends AsyncTask<Void, Void, Boolean> {
        private final String checkMail;
        private final String usrname;
        private final String usermail;
        private final String userpasswd;

        public DatabaseUpdateTask(String checkMail, String usrname, String email, String password) {
            this.checkMail = checkMail;
            this.usrname = usrname;
            this.usermail = email;
            this.userpasswd = password;

        }

        @Override
        protected Boolean doInBackground(Void... params) {
            Connection connection = null;
            PreparedStatement statement = null;
            int rowsAffected = 0;
            boolean success = false;

            try {
                String url = "jdbc:mariadb://3.251.28.226:3306/enigma_db";
                String username = "panelUser";
                String password = "LlodT1Yo1AJIP07Y";

                Class.forName("org.mariadb.jdbc.Driver");
                connection = DriverManager.getConnection(url, username, password);

                String sql = "UPDATE users SET email = ?, username = ?, password_hash = PASSWORD(?) WHERE email = ?";
                statement = connection.prepareStatement(sql);
                statement.setString(1, usermail);
                statement.setString(2, usrname);
                statement.setString(3, userpasswd);
                statement.setString(4, checkMail);
                Log.d("TAG", sql);
                rowsAffected = statement.executeUpdate();
                if (rowsAffected > 0) {
                    success = true;
                }
            } catch (Exception e) {
                e.printStackTrace();
            } finally {
                try {
                    if (statement != null) {
                        statement.close();
                    }
                    if (connection != null) {
                        connection.close();
                    }
                } catch (SQLException e) {
                    e.printStackTrace();
                }
            }

            return success;
        }

        @Override
        protected void onPostExecute(Boolean success) {
            if (success) {
                Toast.makeText(MainActivity2.this, "Data Updated", Toast.LENGTH_SHORT).show();
                Intent intent = new Intent(MainActivity2.this, MainActivity.class);
                startActivity(intent);

            } else {
                Toast.makeText(MainActivity2.this,"Update Failed !!!",Toast.LENGTH_SHORT).show();
            }
        }
    }
    @Override
    protected void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main2);
        String email = getIntent().getStringExtra("email");
        String user = getIntent().getStringExtra("username");



        try {
            Class.forName("org.mariadb.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            e.printStackTrace();
        }

        TextView usr = findViewById(R.id.username);
        TextView mail = findViewById(R.id.email);
        TextView pwd = findViewById(R.id.password);

        MaterialButton loginbtn = findViewById(R.id.updatebtn);

        loginbtn.setOnClickListener(v -> {
            String user1 = usr.getText().toString();
            String email1 = mail.getText().toString();
            String password = pwd.getText().toString();

            new DatabaseUpdateTask(email, user1, email1, password).execute();
        });
    }
}