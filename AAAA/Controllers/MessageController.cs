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
    public class MessageController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal; // Data access layer

        public MessageController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        [HttpPost]
        [Route("SendMessageToStaff")]
        public IActionResult SendMessageToStaff([FromBody] StaffMessage message)
        {
            if (message == null || string.IsNullOrEmpty(message.Subject) || string.IsNullOrEmpty(message.Body))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid message data" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.SendMessageToStaff(message, dbc.GetConn());

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

        [HttpGet]
        [Route("GetStaffMessages")]
        public IActionResult GetStaffMessages()
        {
            DBconention dbc = new DBconention();
            var messages = _dal.GetStaffMessages(dbc.GetConn());

            if (messages != null && messages.Count > 0)
            {
                return Ok(messages);
            }
            else
            {
                return NoContent(); // Returns 204 if no messages are found
            }
        }
    }
}
