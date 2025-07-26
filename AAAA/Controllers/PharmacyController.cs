using System.Data.Common;
using AAAA.Models;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.IdentityModel.Tokens;
using WebApplication5.Models;
using WebApplication5.Util;

namespace WebApplication5.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class PharmacyController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal; // Data access layer

        public PharmacyController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        [HttpPost]
        [Route("AddPharmacy")]
        public IActionResult AddPharmacy([FromBody] Pharmacy pharmacy)
        {
            if (pharmacy == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid Data" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.AddPharmacy(pharmacy, dbc.GetConn());

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

        [HttpPost]
        [Route("PharmacyLogin")]
        public IActionResult PharmacyLogin([FromBody] PharmacyLogin pharmacyLogin)
        {
            if (pharmacyLogin == null || string.IsNullOrEmpty(pharmacyLogin.EMAIL) || string.IsNullOrEmpty(pharmacyLogin.PASSWORD))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid data" });
            }

            try
            {
                DBconention dbc = new DBconention();
                Response response = _dal.PharmacyLogin(pharmacyLogin, dbc.GetConn());

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
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        [HttpGet]
        [Route("GetAllPharmacies")]
        public IActionResult GetAllPharmacies()
        {
            try
            {
                DBconention dbc = new DBconention();
                var pharmacies = _dal.GetAllPharmacies(dbc.GetConn());

                if (pharmacies != null && pharmacies.Any())
                {
                    return Ok(pharmacies);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No pharmacies found" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        [HttpGet]
        [Route("GetPharmacyByEmail")]
        public IActionResult GetPharmacyByEmail([FromQuery] string email)
        {
            if (string.IsNullOrEmpty(email))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Email is required" });
            }

            try
            {
                DBconention dbc = new DBconention();
                Pharmacy pharmacy = _dal.GetPharmacyByEmail(email, dbc.GetConn());

                if (pharmacy != null)
                {
                    return Ok(pharmacy);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "Pharmacy not found" });
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
        [Route("DeletePharmacy/{pharmacyId}")]
        public IActionResult DeletePharmacy(int pharmacyId)
        {
            DBconention dbc = new DBconention();
            Response response = _dal.DeletePharmacy(pharmacyId, dbc.GetConn());

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

        [HttpPost]
        [Route("PlaceOrder")]
        public IActionResult PlaceOrder([FromBody] Order order)
        {
            if (order == null)
            {
                return BadRequest("Invalid order data.");
            }

            Response response = _dal.PlaceOrder(order);

            if (response.StatusCode == 200)
            {
                return Ok(response.StatusMessage);
            }
            else if (response.StatusCode == 400)
            {
                return BadRequest(response.StatusMessage);
            }
            else
            {
                return StatusCode(500, response.StatusMessage);
            }
        }

        [HttpGet]
        [Route("GetAllOrders")]
        public IActionResult GetAllOrders()
        {
            try
            {
                DBconention dbc = new DBconention();
                var orders = _dal.GetAllOrders(dbc.GetConn());

                if (orders != null && orders.Any())
                {
                    return Ok(orders);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No orders found" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        [HttpGet]
        [Route("GetOrdersByPharmacyEmail")]
        public IActionResult GetOrdersByPharmacyEmail(string pharmacyEmail)
        {
            try
            {
                if (string.IsNullOrEmpty(pharmacyEmail))
                {
                    return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid pharmacy email" });
                }

                DBconention dbc = new DBconention();
                var orders = _dal.GetOrdersByPharmacyEmail(pharmacyEmail, dbc.GetConn());

                if (orders != null && orders.Any())
                {
                    return Ok(orders);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No orders found for this pharmacy" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }
        [HttpPost]
        [Route("ConfirmOrder")]
        public IActionResult ConfirmOrder([FromQuery] int orderId)
        {
            if (orderId <= 0)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid order ID" });
            }

            try
            {
                DBconention dbc = new DBconention();
                Response response = _dal.ConfirmOrder(orderId, dbc.GetConn());

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
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }
    }
}
