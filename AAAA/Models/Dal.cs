using System.Reflection;
using AAAA.Models;
using Microsoft.Data.SqlClient;
using WebApplication5.Controllers;
using WebApplication5.Util;

namespace WebApplication5.Models
{
    public class Dal
    {
        public Response AddStaff(Staff staff, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                // Hash the password using BCrypt
                string hashedPassword = BCrypt.Net.BCrypt.HashPassword(staff.PASSWORD);

                // Define the SQL command with parameters
                SqlCommand cmd = new SqlCommand("INSERT INTO STAFF (FIRSTNAME, LASTNAME, EMAIL, PASSWORD, ADDRESS) " +
                                                "VALUES (@firstname, @lastname, @email, @password, @address)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@firstname", staff.FIRSTNAME);
                cmd.Parameters.AddWithValue("@lastname", staff.LASTNAME);
                cmd.Parameters.AddWithValue("@email", staff.EMAIL);
                cmd.Parameters.AddWithValue("@password", hashedPassword); // Store the hashed password
                cmd.Parameters.AddWithValue("@address", staff.ADDRESS);

                // Open connection and execute the command
                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                // Check the result and set response
                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Staff member added successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to add staff member";
                }
            }
            catch (Exception ex)
            {
                // Log the exception message if necessary (not shown here)
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to add staff member: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }


        public Response StaffLogin(StaffLogin staffLogin, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();
            try
            {
                // Define the SQL command to retrieve the hashed password
                SqlCommand cmd = new SqlCommand("SELECT PASSWORD FROM STAFF WHERE EMAIL = @EMAIL", connection);
                cmd.Parameters.AddWithValue("@EMAIL", staffLogin.EMAIL);

                // Open connection and execute the command
                if (connection.State == System.Data.ConnectionState.Closed)
                {
                    connection.Open();
                    SqlDataReader reader = cmd.ExecuteReader();

                    if (reader.HasRows)
                    {
                        reader.Read();
                        string hashedPasswordFromDB = reader["PASSWORD"].ToString();

                        // Verify the password using BCrypt
                        if (BCrypt.Net.BCrypt.Verify(staffLogin.PASSWORD, hashedPasswordFromDB))
                        {
                            response.StatusCode = 200;
                            response.StatusMessage = "Login successful";
                        }
                        else
                        {
                            response.StatusCode = 400;
                            response.StatusMessage = "Invalid credentials";
                        }
                    }
                    else
                    {
                        response.StatusCode = 400;
                        response.StatusMessage = "Invalid credentials";
                    }
                }
            }
            catch (Exception ex)
            {
                // Log the exception message if necessary (not shown here)
                response.StatusCode = 500;
                response.StatusMessage = $"Server error: {ex.Message}";
            }
            finally
            {
                if (connection.State == System.Data.ConnectionState.Open)
                {
                    connection.Close();
                }
            }
            return response;
        }

        public Response AddSupplier(Supplier supplier, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                // Hash the password using BCrypt
                string hashedPassword = BCrypt.Net.BCrypt.HashPassword(supplier.PASSWORD);

                // Define the SQL command with parameters
                SqlCommand cmd = new SqlCommand("INSERT INTO SUPPLIER (NAME, EMAIL, PHONE, ADDRESS, PASSWORD) " +
                                                "VALUES (@name, @email, @phone, @address, @password)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@name", supplier.NAME);
                cmd.Parameters.AddWithValue("@email", supplier.EMAIL);
                cmd.Parameters.AddWithValue("@phone", supplier.PHONE);
                cmd.Parameters.AddWithValue("@address", supplier.ADDRESS);
                cmd.Parameters.AddWithValue("@password", hashedPassword); // Store the hashed password

                // Open connection and execute the command
                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                // Check the result and set response
                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Supplier added successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to add supplier";
                }
            }
            catch (Exception ex)
            {
                // Log the exception message if necessary (not shown here)
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to add supplier: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }


        public Response SupplierLogin(SuplierLogin suplierLogin, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();
            try
            {
                // Define the SQL command to retrieve the hashed password
                SqlCommand cmd = new SqlCommand("SELECT PASSWORD FROM SUPPLIER WHERE EMAIL = @EMAIL", connection);
                cmd.Parameters.AddWithValue("@EMAIL", suplierLogin.EMAIL);

                // Open connection and execute the command
                if (connection.State == System.Data.ConnectionState.Closed)
                {
                    connection.Open();
                    SqlDataReader reader = cmd.ExecuteReader();

                    if (reader.HasRows)
                    {
                        reader.Read();
                        string hashedPasswordFromDB = reader["PASSWORD"].ToString();

                        // Verify the password using BCrypt
                        if (BCrypt.Net.BCrypt.Verify(suplierLogin.PASSWORD, hashedPasswordFromDB))
                        {
                            response.StatusCode = 200;
                            response.StatusMessage = "Login successful";
                        }
                        else
                        {
                            response.StatusCode = 400;
                            response.StatusMessage = "Invalid credentials";
                        }
                    }
                    else
                    {
                        response.StatusCode = 400;
                        response.StatusMessage = "Invalid credentials";
                    }
                }
            }
            catch (Exception ex)
            {
                // Log the exception message if necessary (not shown here)
                response.StatusCode = 500;
                response.StatusMessage = $"Server error: {ex.Message}";
            }
            finally
            {
                if (connection.State == System.Data.ConnectionState.Open)
                {
                    connection.Close();
                }
            }
            return response;
        }

        public Supplier GetSupplierByEmail(string email, SqlConnection connection)
        {
            Supplier supplier = null;
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM SUPPLIER WHERE EMAIL = @email", dbc.GetConn());
                cmd.Parameters.AddWithValue("@email", email);

                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                if (reader.Read())
                {
                    supplier = new Supplier
                    {
                        SUPPLIER_ID = Convert.ToInt32(reader["SUPPLIER_ID"]),
                        NAME = reader["NAME"].ToString(),
                        EMAIL = reader["EMAIL"].ToString(),
                        PHONE = reader["PHONE"].ToString(),
                        ADDRESS = reader["ADDRESS"].ToString()
                    };
                }
            }
            catch (Exception ex)
            {
                // Log error if needed
                throw;
            }
            finally
            {
                dbc.ConClose();
            }

            return supplier;
        }

        public List<Supplier> GetAllSuppliers(SqlConnection connection)
        {
            List<Supplier> supplierList = new List<Supplier>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM SUPPLIER", connection);
                if (connection.State == System.Data.ConnectionState.Closed)
                {
                    connection.Open();
                }
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    Supplier supplier = new Supplier
                    {
                        SUPPLIER_ID = Convert.ToInt32(reader["SUPPLIER_ID"]),
                        NAME = reader["NAME"].ToString(),
                        EMAIL = reader["EMAIL"].ToString(),
                        PHONE = reader["PHONE"].ToString(),
                        ADDRESS = reader["ADDRESS"].ToString(),
                    };
                    supplierList.Add(supplier);
                }
            }
            catch (Exception ex)
            {
                // Handle exception (log or rethrow as needed)
                throw;
            }
            finally
            {
                if (connection.State == System.Data.ConnectionState.Open)
                {
                    connection.Close();
                }
            }
            return supplierList;
        }


        public List<Staff> GetAllStaff(SqlConnection connection)
        {
            List<Staff> staffList = new List<Staff>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM STAFF", connection);
                if (connection.State == System.Data.ConnectionState.Closed)
                {
                    connection.Open();
                }
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    Staff staff = new Staff
                    {
                        STAFF_ID = Convert.ToInt32(reader["STAFF_ID"]),
                        FIRSTNAME = reader["FIRSTName"].ToString(),
                        LASTNAME = reader["LASTNAME"].ToString(),
                        EMAIL = reader["EMAIL"].ToString(),
                        ADDRESS = reader["ADDRESS"].ToString(),
                    };
                    staffList.Add(staff);
                }
            }
            catch ( Exception ex)
            {
                throw;
            } 
          
            finally
            {
                if (connection.State == System.Data.ConnectionState.Open)
                {
                    connection.Close();
                }
            }
            return staffList;
        }

        public Staff GetStaffByEmail(string email, SqlConnection connection)
        {
            Staff staff = null;
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM STAFF WHERE EMAIL = @email", dbc.GetConn());
                cmd.Parameters.AddWithValue("@email", email);

                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                if (reader.Read())
                {
                    staff = new Staff
                    {
                        STAFF_ID = Convert.ToInt32(reader["STAFF_ID"]),
                        FIRSTNAME = reader["FIRSTNAME"].ToString(),
                        LASTNAME = reader["LASTNAME"].ToString(),
                        EMAIL = reader["EMAIL"].ToString(),
                        PASSWORD = reader["PASSWORD"].ToString(), // Ensure you handle security properly
                        ADDRESS = reader["ADDRESS"].ToString()
                    };
                }
            }
            catch (Exception ex)
            {
                // Log error if needed
                throw;
            }
            finally
            {
                dbc.ConClose();
            }

            return staff;
        }

        public Response DeleteSupplier(int supplierId, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("DELETE FROM SUPPLIER WHERE SUPPLIER_ID = @supplierId", dbc.GetConn());
                cmd.Parameters.AddWithValue("@supplierId", supplierId);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Supplier deleted successfully";
                }
                else
                {
                    response.StatusCode = 404;
                    response.StatusMessage = "Supplier not found";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to delete supplier: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public Response AddDrug(Drug drug, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("INSERT INTO DRUG (NAME, CATEGORY, PRICE, QUANTITY) " +
                                                "VALUES (@name, @category, @price, @quantity)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@name", drug.NAME);
                cmd.Parameters.AddWithValue("@category", drug.CATEGORY);
                cmd.Parameters.AddWithValue("@price", drug.PRICE);
                cmd.Parameters.AddWithValue("@quantity", drug.QUANTITY);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Drug added successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to add drug";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to add drug: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public Response UpdateDrug(int id, Drug drug, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("UPDATE DRUG SET NAME = @name, CATEGORY = @category, PRICE = @price, QUANTITY = @quantity " +
                                                "WHERE DRUG_ID = @id", dbc.GetConn());

                cmd.Parameters.AddWithValue("@id", id);
                cmd.Parameters.AddWithValue("@name", drug.NAME);
                cmd.Parameters.AddWithValue("@category", drug.CATEGORY);
                cmd.Parameters.AddWithValue("@price", drug.PRICE);
                cmd.Parameters.AddWithValue("@quantity", drug.QUANTITY);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Drug updated successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to update drug";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to update drug: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }


        public List<Drug> GetAllDrugs(SqlConnection connection)
        {
            List<Drug> drugs = new List<Drug>();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM DRUG", dbc.GetConn());
                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                while (reader.Read())
                {
                    drugs.Add(new Drug
                    {
                        DRUG_ID = Convert.ToInt32(reader["DRUG_ID"]),
                        NAME = reader["NAME"].ToString(),
                        CATEGORY = reader["CATEGORY"].ToString(),
                        PRICE = Convert.ToDecimal(reader["PRICE"]),
                        QUANTITY = Convert.ToInt32(reader["QUANTITY"])
                    });
                }
                dbc.ConClose();
            }
            catch (Exception ex)
            {
                throw new Exception($"Error fetching drugs: {ex.Message}");
            }

            return drugs;
        }


        public List<Drug> SearchDrugByName(string name, SqlConnection connection)
        {
            List<Drug> drugs = new List<Drug>();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM DRUG WHERE NAME LIKE @name", dbc.GetConn());
                cmd.Parameters.AddWithValue("@name", "%" + name + "%");
                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                while (reader.Read())
                {
                    drugs.Add(new Drug
                    {
                        DRUG_ID = Convert.ToInt32(reader["DRUG_ID"]),
                        NAME = reader["NAME"].ToString(),
                        CATEGORY = reader["CATEGORY"].ToString(),
                        PRICE = Convert.ToDecimal(reader["PRICE"]),
                        QUANTITY = Convert.ToInt32(reader["QUANTITY"])
                    });
                }
                dbc.ConClose();
            }
            catch (Exception ex)
            {
                throw new Exception($"Error searching for drugs: {ex.Message}");
            }

            return drugs;
        }

        public Response DeleteDrug(int id, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("DELETE FROM DRUG WHERE DRUG_ID = @id", dbc.GetConn());
                cmd.Parameters.AddWithValue("@id", id);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Drug deleted successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to delete drug (drug not found)";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to delete drug: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }


        public Response AddTender(Tender tender, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                // Define the SQL command with parameters (excluding created_at as it is auto-generated)
                SqlCommand cmd = new SqlCommand("INSERT INTO Tenders (tender_name, drug_name, quantity, specifications, submission_deadline, contract_terms) " +
                                                "VALUES (@tenderName, @drugName, @quantity, @specifications, @submissionDeadline, @contractTerms)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@tenderName", tender.TenderName);
                cmd.Parameters.AddWithValue("@drugName", tender.DrugName);
                cmd.Parameters.AddWithValue("@quantity", tender.Quantity);
                cmd.Parameters.AddWithValue("@specifications", tender.Specifications);
                cmd.Parameters.AddWithValue("@submissionDeadline", tender.SubmissionDeadline);
                cmd.Parameters.AddWithValue("@contractTerms", tender.ContractTerms);

                // Open connection and execute the command
                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                // Check the result and set response
                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Tender added successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to add tender";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to add tender: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        // Update Tender method
        public Response UpdateTender(int id, Tender tender, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                // Define the SQL command to update tender details
                SqlCommand cmd = new SqlCommand("UPDATE Tenders SET tender_name = @tenderName, drug_name = @drugName, quantity = @quantity, " +
                                                "specifications = @specifications, submission_deadline = @submissionDeadline, contract_terms = @contractTerms " +
                                                "WHERE tender_id = @tenderId", dbc.GetConn());

                // Add parameters to prevent SQL injection
                cmd.Parameters.AddWithValue("@tenderName", tender.TenderName);
                cmd.Parameters.AddWithValue("@drugName", tender.DrugName);
                cmd.Parameters.AddWithValue("@quantity", tender.Quantity);
                cmd.Parameters.AddWithValue("@specifications", tender.Specifications);
                cmd.Parameters.AddWithValue("@submissionDeadline", tender.SubmissionDeadline);
                cmd.Parameters.AddWithValue("@contractTerms", tender.ContractTerms);
                cmd.Parameters.AddWithValue("@tenderId", id);

                // Open connection and execute the command
                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                // Check the result and set response
                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Tender updated successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Tender not found or failed to update";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to update tender: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public List<Tender> GetAllTenders(SqlConnection connection)
        {
            List<Tender> tenders = new List<Tender>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT tender_id, tender_name, drug_name, quantity, specifications, submission_deadline, contract_terms, status FROM Tenders", connection);
                connection.Open();
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    Tender tender = new Tender
                    {
                        TenderId = Convert.ToInt32(reader["tender_id"]),
                        TenderName = reader["tender_name"].ToString(),
                        DrugName = reader["drug_name"].ToString(),
                        Quantity = Convert.ToInt32(reader["quantity"]),
                        Specifications = reader["specifications"].ToString(),
                        SubmissionDeadline = Convert.ToDateTime(reader["submission_deadline"]),
                        ContractTerms = reader["contract_terms"].ToString(),
                        Status = reader["status"] == DBNull.Value ? "Active" : reader["status"].ToString() // Handle NULL status
                    };
                    tenders.Add(tender);
                }
            }
            catch (Exception ex)
            {
                throw new Exception($"Error retrieving tenders: {ex.Message}");
            }
            finally
            {
                connection.Close();
            }
            return tenders;
        }

        public Tender GetTenderById(int tenderId, SqlConnection connection)
        {
            Tender tender = null;
            DBconention dbc = new DBconention();

            try
            {
                // Define SQL query to retrieve a single tender by ID
                SqlCommand cmd = new SqlCommand("SELECT tender_id, tender_name, drug_name, quantity, specifications, submission_deadline, contract_terms " +
                                                "FROM Tenders WHERE tender_id = @TenderId", dbc.GetConn());

                // Add parameter to avoid SQL injection
                cmd.Parameters.AddWithValue("@TenderId", tenderId);

                // Open connection
                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                // If a record is found, map the result to a Tender object
                if (reader.Read())
                {
                    tender = new Tender
                    {
                        TenderId = Convert.ToInt32(reader["tender_id"]),
                        TenderName = reader["tender_name"].ToString(),
                        DrugName = reader["drug_name"].ToString(),
                        Quantity = Convert.ToInt32(reader["quantity"]),
                        Specifications = reader["specifications"].ToString(),
                        SubmissionDeadline = (DateTime)(reader["submission_deadline"] as DateTime?),
                        ContractTerms = reader["contract_terms"].ToString()
                    };
                }
                reader.Close();
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error retrieving tender by ID: {ex.Message}");
            }
            finally
            {
                dbc.ConClose();
            }

            return tender;
        }

        public Response DeleteTender(int id, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                // Define the SQL command to delete a tender by ID
                SqlCommand cmd = new SqlCommand("DELETE FROM Tenders WHERE tender_id = @tenderId", dbc.GetConn());

                // Add parameters to prevent SQL injection
                cmd.Parameters.AddWithValue("@tenderId", id);

                // Open connection and execute the command
                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                // Check the result and set response
                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Tender deleted successfully";
                }
                else
                {
                    response.StatusCode = 404;
                    response.StatusMessage = "Tender not found";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to delete tender: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }


        public Response AddProposal(Proposal proposal, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                // Define the SQL command with parameters
                SqlCommand cmd = new SqlCommand("INSERT INTO Tender_Submissions (tender_id, supplier_name, price, delivery_time) " +
                                                "VALUES (@tender_id, @email, @price, @delivery_time)", dbc.GetConn());

                // Adding parameters to SQL command
                cmd.Parameters.AddWithValue("@tender_id", proposal.TenderId);
                cmd.Parameters.AddWithValue("@email", proposal.SupplierName);
                cmd.Parameters.AddWithValue("@price", proposal.Price);
                cmd.Parameters.AddWithValue("@delivery_time", proposal.DeliveryTime);

                // Open connection and execute the command
                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                // Check the result and set the response
                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Proposal added successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to add proposal";
                }
            }
            catch (Exception ex)
            {
                // Log the exception if necessary (not shown here)
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to add proposal: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public List<TenderSubmission> GetAllProposals(SqlConnection connection)
        {
            List<TenderSubmission> proposals = new List<TenderSubmission>();

            try
            {
                SqlCommand cmd = new SqlCommand("SELECT submission_id, tender_id, supplier_name, price, delivery_time, submission_date, status FROM Tender_Submissions", connection);
                connection.Open();
                SqlDataReader reader = cmd.ExecuteReader();

                while (reader.Read())
                {
                    TenderSubmission proposal = new TenderSubmission
                    {
                        TenderId = Convert.ToInt32(reader["tender_id"]),
                        SupplierName = reader["supplier_name"].ToString(),
                        Price = Convert.ToDecimal(reader["price"]),
                        DeliveryTime = Convert.ToInt32(reader["delivery_time"]),
                        SubmissionDate = Convert.ToDateTime(reader["submission_date"]),
                        Status = reader["status"].ToString() ?? "Pending"
                    };
                    proposals.Add(proposal);
                }
            }
            catch (Exception ex)
            {
                // Log the error if needed (not shown here)
                throw new Exception($"Error retrieving proposals: {ex.Message}");
            }

            return proposals;
        }

        public Response AcceptProposal(int tenderId, SqlConnection connection)
        {
            Response response = new Response();
            try
            {
                // Update the proposal status to 'Accepted'
                SqlCommand cmd = new SqlCommand(
                    "UPDATE Tender_Submissions " +
                    "SET status = 'Accepted' " +
                    "WHERE tender_id = @tender_id", connection);
                cmd.Parameters.AddWithValue("@tender_id", tenderId);

                // Update the tender status to 'Awarded'
                SqlCommand tenderCmd = new SqlCommand(
                    "UPDATE Tenders " +
                    "SET status = 'Awarded' " +
                    "WHERE tender_id = @tender_id", connection);
                tenderCmd.Parameters.AddWithValue("@tender_id", tenderId);

                connection.Open();
                SqlTransaction transaction = connection.BeginTransaction();
                try
                {
                    cmd.Transaction = transaction;
                    tenderCmd.Transaction = transaction;

                    int proposalUpdated = cmd.ExecuteNonQuery();
                    int tenderUpdated = tenderCmd.ExecuteNonQuery();

                    if (proposalUpdated > 0 && tenderUpdated > 0)
                    {
                        transaction.Commit();
                        response.StatusCode = 200;
                        response.StatusMessage = "Proposal accepted and tender awarded successfully";
                    }
                    else
                    {
                        transaction.Rollback();
                        response.StatusCode = 400;
                        response.StatusMessage = "Failed to accept proposal or update tender status";
                    }
                }
                catch (Exception)
                {
                    transaction.Rollback();
                    throw;
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Error accepting proposal: {ex.Message}";
            }
            finally
            {
                connection.Close();
            }
            return response;
        }

        public List<TenderSubmission> GetProposalsBySupplier(string email, SqlConnection connection)
        {
            List<TenderSubmission> proposals = new List<TenderSubmission>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT submission_id, tender_id, supplier_name, price, delivery_time, submission_date, status FROM Tender_Submissions WHERE supplier_name = @email", connection);
                cmd.Parameters.AddWithValue("@email", email);
                connection.Open();
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    TenderSubmission proposal = new TenderSubmission
                    {
                        TenderId = Convert.ToInt32(reader["tender_id"]),
                        SupplierName = reader["supplier_name"].ToString(),
                        Price = Convert.ToDecimal(reader["price"]),
                        DeliveryTime = Convert.ToInt32(reader["delivery_time"]),
                        SubmissionDate = Convert.ToDateTime(reader["submission_date"]),
                        Status = reader["status"].ToString() ?? "Pending"
                    };
                    proposals.Add(proposal);
                }
            }
            catch (Exception ex)
            {
                throw new Exception($"Error retrieving proposals: {ex.Message}");
            }
            return proposals;
        }

        public Response AddPharmacy(Pharmacy pharmacy, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                // Hash the password using BCrypt
                string hashedPassword = BCrypt.Net.BCrypt.HashPassword(pharmacy.PASSWORD);

                // Define the SQL command with parameters
                SqlCommand cmd = new SqlCommand("INSERT INTO PHARMACY (NAME, EMAIL, PASSWORD, ADDRESS, CONTACT_NUMBER) " +
                                                "VALUES (@name, @email, @password, @address, @contactNumber)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@name", pharmacy.NAME);
                cmd.Parameters.AddWithValue("@email", pharmacy.EMAIL);
                cmd.Parameters.AddWithValue("@password", hashedPassword); // Store the hashed password
                cmd.Parameters.AddWithValue("@address", pharmacy.ADDRESS);
                cmd.Parameters.AddWithValue("@contactNumber", pharmacy.CONTACT_NUMBER);

                // Open connection and execute the command
                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                // Check the result and set response
                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Pharmacy added successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to add pharmacy";
                }
            }
            catch (Exception ex)
            {
                // Log the exception message if necessary (not shown here)
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to add pharmacy: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        // Pharmacy Login method
        public Response PharmacyLogin(PharmacyLogin pharmacyLogin, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();
            try
            {
                // Define the SQL command to retrieve the hashed password
                SqlCommand cmd = new SqlCommand("SELECT PASSWORD FROM PHARMACY WHERE EMAIL = @EMAIL", connection);
                cmd.Parameters.AddWithValue("@EMAIL", pharmacyLogin.EMAIL);

                // Open connection and execute the command
                if (connection.State == System.Data.ConnectionState.Closed)
                {
                    connection.Open();
                    SqlDataReader reader = cmd.ExecuteReader();

                    if (reader.HasRows)
                    {
                        reader.Read();
                        string hashedPasswordFromDB = reader["PASSWORD"].ToString();

                        // Verify the password using BCrypt
                        if (BCrypt.Net.BCrypt.Verify(pharmacyLogin.PASSWORD, hashedPasswordFromDB))
                        {
                            response.StatusCode = 200;
                            response.StatusMessage = "Login successful";
                        }
                        else
                        {
                            response.StatusCode = 400;
                            response.StatusMessage = "Invalid credentials";
                        }
                    }
                    else
                    {
                        response.StatusCode = 400;
                        response.StatusMessage = "Invalid credentials";
                    }
                }
            }
            catch (Exception ex)
            {
                // Log the exception message if necessary (not shown here)
                response.StatusCode = 500;
                response.StatusMessage = $"Server error: {ex.Message}";
            }
            finally
            {
                if (connection.State == System.Data.ConnectionState.Open)
                {
                    connection.Close();
                }
            }
            return response;
        }

        // Get All Pharmacies Method
        public List<Pharmacy> GetAllPharmacies(SqlConnection connection)
        {
            List<Pharmacy> pharmacies = new List<Pharmacy>();
            DBconention dbc = new DBconention();

            try
            {
                // Define the SQL command to select all pharmacies
                SqlCommand cmd = new SqlCommand("SELECT * FROM PHARMACY", dbc.GetConn());

                // Open connection and execute the command
                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                // Loop through the reader and create Pharmacy objects
                while (reader.Read())
                {
                    Pharmacy pharmacy = new Pharmacy
                    {
                        PHARMACY_ID = (int)reader["PHARMACY_ID"],
                        NAME = reader["NAME"].ToString(),
                        EMAIL = reader["EMAIL"].ToString(),
                        PASSWORD = reader["PASSWORD"].ToString(),
                        ADDRESS = reader["ADDRESS"].ToString(),
                        CONTACT_NUMBER = reader["CONTACT_NUMBER"].ToString()
                    };
                    pharmacies.Add(pharmacy);
                }

                dbc.ConClose();
            }
            catch (Exception ex)
            {
                // Handle exceptions and log if necessary
                throw new Exception($"Error fetching pharmacies: {ex.Message}");
            }
            finally
            {
                dbc.ConClose();
            }

            return pharmacies;
        }

        public Pharmacy GetPharmacyByEmail(string email, SqlConnection connection)
        {
            Pharmacy pharmacy = null;
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM PHARMACY WHERE EMAIL = @email", dbc.GetConn());
                cmd.Parameters.AddWithValue("@email", email);

                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                if (reader.Read())
                {
                    pharmacy = new Pharmacy
                    {
                        PHARMACY_ID = Convert.ToInt32(reader["PHARMACY_ID"]),
                        NAME = reader["NAME"].ToString(),
                        EMAIL = reader["EMAIL"].ToString(),
                        ADDRESS = reader["ADDRESS"].ToString(),
                        CONTACT_NUMBER = reader["CONTACT_NUMBER"].ToString()
                    };
                }
            }
            catch (Exception ex)
            {
                // Log error if needed
                throw;
            }
            finally
            {
                dbc.ConClose();
            }

            return pharmacy;
        }

        public Response DeletePharmacy(int pharmacyId, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("DELETE FROM PHARMACY WHERE PHARMACY_ID = @pharmacyId", dbc.GetConn());
                cmd.Parameters.AddWithValue("@pharmacyId", pharmacyId);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Pharmacy deleted successfully";
                }
                else
                {
                    response.StatusCode = 404;
                    response.StatusMessage = "Pharmacy not found";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to delete pharmacy: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public Response PlaceOrder(Order order)
        {
            Response response = new Response();
            DBconention dbc = new DBconention(); // DB connection manager

            try
            {
                // Query to check if the drug exists and has enough stock
                SqlCommand cmdCheckStock = new SqlCommand("SELECT Quantity FROM DRUG WHERE DRUG_ID = @drugId", dbc.GetConn());
                cmdCheckStock.Parameters.AddWithValue("@drugId", order.DrugId);

                dbc.ConOpen();
                var stockQuantity = cmdCheckStock.ExecuteScalar();
                dbc.ConClose();

                if (stockQuantity == null || (int)stockQuantity < order.Quantity)
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Not enough stock available for this drug.";
                    return response;
                }

                // Query to place the order, assuming the total price is already provided by the client
                SqlCommand cmdPlaceOrder = new SqlCommand("INSERT INTO Orders (DrugId, Quantity, PharmacyEmail, OrderDate, TotalPrice) VALUES (@drugId, @quantity, @pharmacyEmail, GETDATE(), @totalPrice)", dbc.GetConn());
                cmdPlaceOrder.Parameters.AddWithValue("@drugId", order.DrugId);
                cmdPlaceOrder.Parameters.AddWithValue("@quantity", order.Quantity);
                cmdPlaceOrder.Parameters.AddWithValue("@pharmacyEmail", order.PharmacyEmail);
                cmdPlaceOrder.Parameters.AddWithValue("@totalPrice", order.TotalPrice); // Total price is now part of the order request

                dbc.ConOpen();
                int rowsAffected = cmdPlaceOrder.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Order placed successfully!";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to place order.";
                }
            }
            catch (Exception ex)
            {
                // Log the exception message
                response.StatusCode = 500;
                response.StatusMessage = $"Error occurred while placing order: {ex.Message}";
            }
            return response;
        }

        public Response ConfirmOrder(int orderId, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand(
                    "UPDATE Orders SET Status = 'Confirmed' WHERE OrderId = @OrderId", dbc.GetConn());

                cmd.Parameters.AddWithValue("@OrderId", orderId);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Order confirmed successfully!";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to confirm order.";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Error confirming order: {ex.Message}";
            }

            return response;
        }


        // Get All Orders Method
        public List<OrderRequest> GetAllOrders(SqlConnection connection)
        {
            List<OrderRequest> orders = new List<OrderRequest>();
            DBconention dbc = new DBconention();

            try
            {

                // Define the SQL command to select all orders, including TotalPrice
                SqlCommand cmd = new SqlCommand("SELECT * FROM Orders", dbc.GetConn());

                // Open connection and execute the command
                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                // Loop through the reader and create Order objects
                while (reader.Read())
                {
                    OrderRequest order = new OrderRequest
                    {
                        OrderId = (int)reader["OrderId"],
                        DrugId = (int)reader["DrugId"],
                        Quantity = (int)reader["Quantity"],
                        PharmacyEmail = reader["PharmacyEmail"].ToString(),
                        OrderDate = (DateTime)reader["OrderDate"],
                        TotalPrice = reader["TotalPrice"] != DBNull.Value ? (decimal)reader["TotalPrice"] : 0,
                        Status = reader["Status"].ToString()
                    };
                    orders.Add(order);
                }

                dbc.ConClose();
            }
            catch (Exception ex)
            {
                // Handle exceptions and log if necessary
                throw new Exception($"Error fetching orders: {ex.Message}");
            }
            finally
            {
                dbc.ConClose();
            }

            return orders;
        }

        public List<OrderRequest> GetOrdersByPharmacyEmail(string pharmacyEmail, SqlConnection connection)
        {
            List<OrderRequest> orders = new List<OrderRequest>();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM Orders WHERE PharmacyEmail = @PharmacyEmail", dbc.GetConn());
                cmd.Parameters.AddWithValue("@PharmacyEmail", pharmacyEmail);

                dbc.ConOpen();
                SqlDataReader reader = cmd.ExecuteReader();

                while (reader.Read())
                {
                    OrderRequest order = new OrderRequest
                    {
                        OrderId = (int)reader["OrderId"],
                        DrugId = (int)reader["DrugId"],
                        Quantity = (int)reader["Quantity"],
                        PharmacyEmail = reader["PharmacyEmail"].ToString(),
                        OrderDate = (DateTime)reader["OrderDate"],
                        TotalPrice = reader["TotalPrice"] != DBNull.Value ? (decimal)reader["TotalPrice"] : 0,
                        Status = reader["Status"].ToString()
                    };
                    orders.Add(order);
                }

                dbc.ConClose();
            }
            catch (Exception ex)
            {
                throw new Exception($"Error fetching orders for pharmacy {pharmacyEmail}: {ex.Message}");
            }
            finally
            {
                dbc.ConClose();
            }

            return orders;
        }

        public Response RequestDrug(DrugRequest request, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("INSERT INTO DrugRequests (DrugName, DrugCategory, Quantity, PharmacyEmail, RequestDate) " +
                                                "VALUES (@DrugName, @DrugCategory, @Quantity, @PharmacyEmail, @RequestDate)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@DrugName", request.DrugName);
                cmd.Parameters.AddWithValue("@DrugCategory", request.DrugCategory);
                cmd.Parameters.AddWithValue("@Quantity", request.Quantity);
                cmd.Parameters.AddWithValue("@PharmacyEmail", request.PharmacyEmail);
                cmd.Parameters.AddWithValue("@RequestDate", DateTime.Now);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Drug request submitted successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to submit drug request";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to submit drug request: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public List<DrugRequest> GetAllDrugRequests(SqlConnection connection)
        {
            List<DrugRequest> drugRequests = new List<DrugRequest>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM DrugRequests", connection);
                connection.Open();
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    DrugRequest request = new DrugRequest
                    {
                        RequestId = reader.GetInt32(0),
                        DrugName = reader.GetString(1),
                        DrugCategory = reader.GetString(2),
                        Quantity = reader.GetInt32(3),
                        PharmacyEmail = reader.GetString(4),
                        RequestDate = reader.GetDateTime(5),
                    };
                    drugRequests.Add(request);
                }
            }
            catch (Exception ex)
            {
                throw new Exception($"Error retrieving drug requests: {ex.Message}");
            }
            finally
            {
                connection.Close();
            }
            return drugRequests;
        }

        public Response DeleteDrugRequest(int requestId, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("DELETE FROM DrugRequests WHERE RequestId = @RequestId", dbc.GetConn());
                cmd.Parameters.AddWithValue("@RequestId", requestId);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Drug request deleted successfully";
                }
                else
                {
                    response.StatusCode = 404;
                    response.StatusMessage = "Drug request not found";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to delete drug request: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public Response SendMessageToStaff(StaffMessage message, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("INSERT INTO StaffMessages (Subject, Body, SentDate) " +
                                                "VALUES (@Subject, @Body, @SentDate)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@Subject", message.Subject);
                cmd.Parameters.AddWithValue("@Body", message.Body);
                cmd.Parameters.AddWithValue("@SentDate", DateTime.Now);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Message sent successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to send message";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to send message: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }

        public List<StaffMessage> GetStaffMessages(SqlConnection connection)
        {
            List<StaffMessage> messages = new List<StaffMessage>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM StaffMessages ORDER BY SentDate DESC", connection);
                connection.Open();
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    StaffMessage message = new StaffMessage
                    {
                        MessageId = reader.GetInt32(0),
                        Subject = reader.GetString(1),
                        Body = reader.GetString(2),
                        SentDate = reader.GetDateTime(3)
                    };
                    messages.Add(message);
                }
            }
            catch (Exception ex)
            {
                throw new Exception($"Error retrieving messages: {ex.Message}");
            }
            finally
            {
                connection.Close();
            }
            return messages;
        }
        public List<StaffMessage> GetMessages(SqlConnection connection)
        {
            List<StaffMessage> messages = new List<StaffMessage>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM StaffMessages ORDER BY SentDate DESC", connection);
                connection.Open();
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    StaffMessage message = new StaffMessage
                    {
                        MessageId = reader.GetInt32(0),
                        Subject = reader.GetString(1),
                        Body = reader.GetString(2),
                        SentDate = reader.GetDateTime(3)
                    };
                    messages.Add(message);
                }
            }
            catch (Exception ex)
            {
                throw new Exception($"Error retrieving messages: {ex.Message}");
            }
            finally
            {
                connection.Close();
            }
            return messages;
        }
        public Response ReplyToMessage(Reply reply, SqlConnection connection)
        {
            Response response = new Response();
            DBconention dbc = new DBconention();

            try
            {
                SqlCommand cmd = new SqlCommand("INSERT INTO Replies (MessageId, ReplyText, ReplierEmail, ReplyDate) " +
                                                "VALUES (@MessageId, @ReplyText, @ReplierEmail, @ReplyDate)", dbc.GetConn());

                cmd.Parameters.AddWithValue("@MessageId", reply.MessageId);
                cmd.Parameters.AddWithValue("@ReplyText", reply.ReplyText);
                cmd.Parameters.AddWithValue("@ReplierEmail", reply.ReplierEmail);
                cmd.Parameters.AddWithValue("@ReplyDate", DateTime.Now);

                dbc.ConOpen();
                int rowsAffected = cmd.ExecuteNonQuery();
                dbc.ConClose();

                if (rowsAffected > 0)
                {
                    response.StatusCode = 200;
                    response.StatusMessage = "Reply sent successfully";
                }
                else
                {
                    response.StatusCode = 400;
                    response.StatusMessage = "Failed to send reply";
                }
            }
            catch (Exception ex)
            {
                response.StatusCode = 500;
                response.StatusMessage = $"Failed to send reply: {ex.Message}";
            }
            finally
            {
                dbc.ConClose();
            }

            return response;
        }
        public List<Reply> GetRepliesForMessage(int messageId, SqlConnection connection)
        {
            List<Reply> replies = new List<Reply>();
            try
            {
                SqlCommand cmd = new SqlCommand("SELECT * FROM Replies WHERE MessageId = @MessageId ORDER BY ReplyDate ASC", connection);
                cmd.Parameters.AddWithValue("@MessageId", messageId);

                connection.Open();
                SqlDataReader reader = cmd.ExecuteReader();
                while (reader.Read())
                {
                    Reply reply = new Reply
                    {
                        ReplyId = reader.GetInt32(0),
                        MessageId = reader.GetInt32(1),
                        ReplyText = reader.GetString(2),
                        ReplierEmail = reader.GetString(3),
                        ReplyDate = reader.GetDateTime(4)
                    };
                    replies.Add(reply);
                }
            }
            catch (Exception ex)
            {
                throw new Exception($"Error retrieving replies: {ex.Message}");
            }
            finally
            {
                connection.Close();
            }
            return replies;
        }
    }
}
