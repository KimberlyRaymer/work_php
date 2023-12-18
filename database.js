import mysql from 'mysql2'
import dotenv from 'dotenv'
dotenv.config()

// pool is a collection of connections to the database
// does not need to connect for every query, rather just collects them
// check .env file to change these variables to your database
const pool = mysql.createPool({
    host: process.env.MYSQL_HOST,
    user: process.env.MYSQL_USER,
    password: process.env.MYSQL_PASSWORD,
    database: process.env.MYSQL_DATABASE
}).promise()

// generate file that has the specific sql query
// ? is to make sure the query is incomplete, prevents sql injection attacks
export async function getFile(feat_src_v, target_src_v) {
    //change 'soybase' to the SQL table needed
    const [results] = await pool.query(`SELECT * FROM soybase WHERE feature_source_version = ? AND target_source_version = ?`, [feat_src_v, target_src_v])
    return results
}
