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
    public class ReplyController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal; // Data access layer

        public ReplyController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        [HttpGet]
        [Route("GetMessages")]
        public IActionResult GetMessages()
        {
            DBconention dbc = new DBconention();
            var messages = _dal.GetMessages(dbc.GetConn());

            if (messages != null && messages.Count > 0)
            {
                return Ok(messages);
            }
            else
            {
                return NoContent(); // Returns 204 if no messages are found
            }
        }

        [HttpPost]
        [Route("ReplyToMessage")]
        public IActionResult ReplyToMessage([FromBody] Reply reply)
        {
            if (reply == null || string.IsNullOrEmpty(reply.ReplyText))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid reply data" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.ReplyToMessage(reply, dbc.GetConn());

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
        [Route("GetRepliesForMessage/{messageId}")]
        public IActionResult GetRepliesForMessage(int messageId)
        {
            DBconention dbc = new DBconention();
            var replies = _dal.GetRepliesForMessage(messageId, dbc.GetConn());

            if (replies != null && replies.Count > 0)
            {
                return Ok(replies);
            }
            else
            {
                return NoContent(); // Returns 204 if no replies are found
            }
        }
    }
}