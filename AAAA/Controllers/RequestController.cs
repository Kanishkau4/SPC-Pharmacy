using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using WebApplication5.Models;
using WebApplication5.Util;
using System;
using System.Linq;
using AAAA.Models;

namespace AAAA.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class RequestController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal;

        public RequestController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        [HttpPost]
        [Route("RequestDrug")]
        public IActionResult RequestDrug([FromBody] DrugRequest request)
        {
            if (request == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid request data" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.RequestDrug(request, dbc.GetConn());

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
        [Route("GetAllDrugRequests")]
        public IActionResult GetAllDrugRequests()
        {
            DBconention dbc = new DBconention();
            var drugRequests = _dal.GetAllDrugRequests(dbc.GetConn());

            if (drugRequests != null && drugRequests.Count > 0)
            {
                return Ok(drugRequests);
            }
            else
            {
                return NoContent(); // Returns 204 if no drug requests are found
            }
        }

        [HttpDelete]
        [Route("DeleteDrugRequest/{requestId}")]
        public IActionResult DeleteDrugRequest(int requestId)
        {
            if (requestId <= 0)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid request ID" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.DeleteDrugRequest(requestId, dbc.GetConn());

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
