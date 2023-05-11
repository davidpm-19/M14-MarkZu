package com.example.theVault;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.google.android.material.button.MaterialButton;

import java.sql.*;




public class MainActivity extends AppCompatActivity {

    public class DatabaseQueryTask extends AsyncTask<Void, Void, Boolean> {
        private final String usermail;
        private final String userpasswd;
        private String unm;
        private String uml;
        private int isRoot;



        public DatabaseQueryTask(String email, String password) {
            this.usermail = email;
            this.userpasswd = password;
        }

        @Override
        protected Boolean doInBackground(Void... params) {
            Connection connection = null;
            PreparedStatement statement = null;
            ResultSet resultSet = null;
            boolean success = false;

            try {
                String url = "jdbc:mariadb://3.251.28.226:3306/enigma_db";
                String username = "dashboardUser";
                String password = "9@@2Zmaf(v2!2sJ-";

                Class.forName("org.mariadb.jdbc.Driver");
                connection = DriverManager.getConnection(url, username, password);

                String sql = "SELECT email, username, is_root FROM users WHERE email = ? AND password_hash = PASSWORD(?)";
                statement = connection.prepareStatement(sql);
                statement.setString(1, usermail);
                statement.setString(2, userpasswd);
                resultSet = statement.executeQuery();
                if (resultSet.next()) {
                    uml = resultSet.getString("email");
                    unm = resultSet.getString("username");
                    isRoot = resultSet.getInt("is_root");
                    if (unm != null){
                        success = true;
                    }
                } else {
                    Log.d("TAG", "No results found.");
                }
            } catch (Exception e) {
                e.printStackTrace();
            } finally {
                try {
                    if (resultSet != null) {
                        resultSet.close();
                    }
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
                Toast.makeText(MainActivity.this, "LOGIN SUCCESSFUL", Toast.LENGTH_SHORT).show();
                if (isRoot == 0) {
                    Log.d("TAG", String.valueOf(isRoot));
                    Intent intent = new Intent(MainActivity.this, MainActivity2.class);
                    intent.putExtra("email", uml);
                    intent.putExtra("username", unm);
                    try {
                        startActivity(intent);
                    }catch (Exception e){
                        e.printStackTrace();

                    }
                }else{
                    Intent intent = new Intent(MainActivity.this, MainActivity3.class);
                    try {
                        startActivity(intent);
                    }catch (Exception e){
                        e.printStackTrace();

                    }
                }
            } else {
                Toast.makeText(MainActivity.this,"LOGIN FAILED !!!",Toast.LENGTH_SHORT).show();
            }
        }
    }
    @Override
    protected void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);



        try {
            Class.forName("org.mariadb.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            e.printStackTrace();
        }


        TextView mail = findViewById(R.id.email);
        TextView pwd = findViewById(R.id.password);

        MaterialButton loginbtn = findViewById(R.id.loginbtn);

        ImageView enigma = findViewById(R.id.Logo);
        enigma.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent reg = new Intent(MainActivity.this, MainActivity4.class);
                try {
                    startActivity(reg);
                }catch (Exception e) {
                    e.printStackTrace();
                }
            }
        });

        loginbtn.setOnClickListener(v -> {
            String email = mail.getText().toString();
            String password = pwd.getText().toString();

            new DatabaseQueryTask(email, password).execute();
        });
    }
}