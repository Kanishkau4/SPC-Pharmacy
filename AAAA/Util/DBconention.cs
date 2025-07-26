using Microsoft.Data.SqlClient;

namespace WebApplication5.Util
{
    public class DBconention
    {
        private SqlConnection connection;
        public DBconention() 
        {
            var Constring = new ConfigurationBuilder().AddJsonFile("appsettings.json").Build().GetSection("ConnectionStrings")["$con"];
            connection = new SqlConnection(Constring);
        } 
        public SqlConnection GetConn()
        {
            return connection;
        }
        public void ConOpen()
        {
            connection.Open();
        }
        public void ConClose()
        {
            if (connection.State == System.Data.ConnectionState.Open)
            {
                connection.Close();
            }
    }   }
}
