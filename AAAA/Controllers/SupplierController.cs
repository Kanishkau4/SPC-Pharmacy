using AAAA.Models;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using WebApplication5.Models;
using WebApplication5.Util;

namespace WebApplication5.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class SupplierController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal;

        public SupplierController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        // Add Supplier
        [HttpPost]
        [Route("AddSupplier")]
        public IActionResult AddSupplier([FromBody] Supplier supplier)
        {
            if (supplier == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid Data" });
            }

            // Validate that the required fields are not empty
            if (string.IsNullOrWhiteSpace(supplier.NAME) ||
                string.IsNullOrWhiteSpace(supplier.EMAIL) ||
                string.IsNullOrWhiteSpace(supplier.PHONE) ||
                string.IsNullOrWhiteSpace(supplier.ADDRESS) ||
                string.IsNullOrWhiteSpace(supplier.PASSWORD))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "All fields are required" });
            }

            // Proceed with adding the supplier if all validations pass
            DBconention dbc = new DBconention();
            Response response = _dal.AddSupplier(supplier, dbc.GetConn());

            if (response.StatusCode == 200)
            {
                return Ok(response);
            }
            else if (response.StatusCode == 400)
            {
                return BadRequest(response);
            }
            else
            {
                return StatusCode(StatusCodes.Status500InternalServerError, response);
            }
        }


        // Supplier Login
        [HttpPost]
        [Route("SupplierLogin")]
        public IActionResult SupplierLogin([FromBody] SuplierLogin suplierLogin)
        {
            if (suplierLogin == null || string.IsNullOrEmpty(suplierLogin.EMAIL) || string.IsNullOrEmpty(suplierLogin.PASSWORD))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid data" });
            }
            try
            {
                DBconention dbc = new DBconention();
                Response response = _dal.SupplierLogin(suplierLogin, dbc.GetConn());
                if (response.StatusCode == 200)
                {
                    return Ok(response);
                }
                else if (response.StatusCode == 401)
                {
                    return BadRequest(response);
                }
                else
                {
                    return StatusCode(StatusCodes.Status500InternalServerError, response);
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        // Get Supplier by Email
        [HttpGet]
        [Route("GetSupplierByEmail")]
        public IActionResult GetSupplierByEmail([FromQuery] string email)
        {
            if (string.IsNullOrEmpty(email))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Email is required" });
            }

            try
            {
                DBconention dbc = new DBconention();
                Supplier supplier = _dal.GetSupplierByEmail(email, dbc.GetConn());

                if (supplier != null)
                {
                    return Ok(supplier);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "Supplier not found" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError,
                    new Response
                    {
                        StatusCode = 500,
                        StatusMessage = $"Internal server error: {ex.Message}"
                    });
            }
        }

        // Get All Suppliers
        [HttpGet]
        [Route("GetAllSuppliers")]
        public IActionResult GetAllSuppliers()
        {
            try
            {
                DBconention dbc = new DBconention();
                List<Supplier> suppliers = _dal.GetAllSuppliers(dbc.GetConn());

                if (suppliers != null && suppliers.Count > 0)
                {
                    return Ok(suppliers);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No suppliers found" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError,
                    new Response
                    {
                        StatusCode = 500,
                        StatusMessage = $"Internal server error: {ex.Message}"
                    });
            }
        }

        [HttpDelete]
        [Route("DeleteSupplier/{supplierId}")]
        public IActionResult DeleteSupplier(int supplierId)
        {
            DBconention dbc = new DBconention();
            Response response = _dal.DeleteSupplier(supplierId, dbc.GetConn());

            if (response.StatusCode == 200)
            {
                return Ok(response);
            }
            else if (response.StatusCode == 404)
            {
                return NotFound(response);
            }
            else
            {
                return StatusCode(StatusCodes.Status500InternalServerError, response);
            }
        }
    }
}
