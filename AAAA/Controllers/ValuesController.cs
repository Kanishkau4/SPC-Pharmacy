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
    public class StaffController : ControllerBase

    {

        private readonly IConfiguration _configuration;
        private Dal _dal; //data access layer

        public StaffController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }
        [HttpPost]
        [Route("Addstaff")]
        public IActionResult AddStaff([FromBody] Staff staff)
        {
            if (staff == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid Data" });
            }
            DBconention dbc = new DBconention();
            Response response = _dal.AddStaff(staff, dbc.GetConn());

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
        [Route("StaffLogin")]
        public IActionResult StaffLogin([FromBody] StaffLogin staffLogin)
        {
            if (staffLogin == null || string.IsNullOrEmpty(staffLogin.EMAIL) || string.IsNullOrEmpty(staffLogin.PASSWORD))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invaloid data" });
            }
            try
            {
                DBconention dbc = new DBconention();
                Response response = _dal.StaffLogin(staffLogin, dbc.GetConn());
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

        [HttpGet]
        [Route("GetAllStaff")]

        public IActionResult GetAllStaff()
        {
            try
            {
                DBconention dbc = new DBconention();
                var staffList = _dal.GetAllStaff(dbc.GetConn());
                if (staffList != null && staffList.Any())
                {
                    return Ok(staffList);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No staff Members" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        // Get Staff by Email
        [HttpGet]
        [Route("GetStaffByEmail")]
        public IActionResult GetStaffByEmail([FromQuery] string email)
        {
            if (string.IsNullOrEmpty(email))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Email is required" });
            }

            try
            {
                DBconention dbc = new DBconention();
                Staff staff = _dal.GetStaffByEmail(email, dbc.GetConn());

                if (staff != null)
                {
                    return Ok(staff);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "Staff not found" });
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


    }
}
