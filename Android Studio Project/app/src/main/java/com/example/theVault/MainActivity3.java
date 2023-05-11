package com.example.theVault;

import androidx.appcompat.app.AppCompatActivity;

import android.annotation.SuppressLint;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.widget.Switch;
import android.widget.TextView;
import android.widget.Toast;

import com.google.android.material.button.MaterialButton;

import java.sql.*;




public class MainActivity3 extends AppCompatActivity {

    public class DatabaseUpdateTask extends AsyncTask<Void, Void, Boolean> {
        private final String book_id;
        private final String group_id;
        private final String group_won;

        public DatabaseUpdateTask(String group_won, String book_id, String group_id) {
            this.book_id = book_id;
            this.group_id = group_id;
            this.group_won = group_won;
        }

        @Override
        protected Boolean doInBackground(Void... params) {
            Connection connection = null;
            PreparedStatement statement = null;
            int rowsAffected = 0;
            int index = 0;
            boolean success = false;

            try {
                String url = "jdbc:mariadb://3.251.28.226:3306/enigma_db";
                String username = "panelUser";
                String password = "LlodT1Yo1AJIP07Y";

                Class.forName("org.mariadb.jdbc.Driver");
                connection = DriverManager.getConnection(url, username, password);

                int bid = Integer.parseInt(book_id);
                int gid = Integer.parseInt(group_id);

                String indexSql = "SELECT JSON_SEARCH(group_data, 'one', ? , NULL, '$[*].book_id') AS json_path FROM groups WHERE group_id = ?";
                PreparedStatement indexSt = connection.prepareStatement(indexSql);
                indexSt.setInt(1, bid);
                indexSt.setInt(2, gid);
                ResultSet resultSet = indexSt.executeQuery();
                if (resultSet.next()) {
                    String jsonPath = resultSet.getString("json_path");
                    if (jsonPath != null) {
                        index = Integer.parseInt(jsonPath.substring(3, jsonPath.indexOf("]")));
                            Log.d("TAG", String.valueOf(index));
                    } else {
                            Log.d("TAG", "no");
                    }
                }
                indexSt.close();
                String sql = "UPDATE groups SET group_data = JSON_SET( group_data, CONCAT('$[', ?, '].group_won'), ?) WHERE JSON_EXTRACT(group_data, CONCAT('$[', ?, '].book_id')) = ?" +
                        " AND group_id = ?;";
                statement = connection.prepareStatement(sql);
                statement.setInt(1, index);
                statement.setString(2, group_won);
                statement.setInt(3, index);
                statement.setInt(4, bid);
                statement.setInt(5, gid);
                rowsAffected = statement.executeUpdate();
                Log.d("TAGTAG", "MESSAGE: " + rowsAffected);

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
                Toast.makeText(MainActivity3.this, "Data Updated", Toast.LENGTH_SHORT).show();

            } else {
                Toast.makeText(MainActivity3.this,"Update Failed !!!",Toast.LENGTH_SHORT).show();
            }
        }
    }
    @Override
    protected void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main3);

        try {
            Class.forName("org.mariadb.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            e.printStackTrace();
        }

        TextView gid = findViewById(R.id.group_id);
        TextView bid = findViewById(R.id.book_id);
        @SuppressLint("UseSwitchCompatOrMaterialCode") Switch won = findViewById(R.id.won_check);



        MaterialButton loginbtn = findViewById(R.id.updatebtn);
        loginbtn.setOnClickListener(v -> {
            String groupid = gid.getText().toString();
            String bookid = bid.getText().toString();
            boolean groupwon = won.isChecked();
            String group_won = groupwon ? "yes" : "no";
            new DatabaseUpdateTask(group_won, bookid, groupid).execute();
        });
    }
}