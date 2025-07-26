using AAAA.Models;
using Microsoft.AspNetCore.Mvc;
using WebApplication5.Util;
using System.Data.SqlClient;
using WebApplication5.Models;

namespace WebApplication5.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class TenderController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal; // Data access layer

        public TenderController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        // Add Tender
        [HttpPost]
        [Route("AddTender")]
        public IActionResult AddTender([FromBody] Tender tender)
        {
            if (tender == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid tender data" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.AddTender(tender, dbc.GetConn());

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

        // Update Tender
        [HttpPut]
        [Route("UpdateTender/{id}")]
        public IActionResult UpdateTender(int id, [FromBody] Tender tender)
        {
            if (tender == null || id != tender.TenderId)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Tender data is invalid" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.UpdateTender(id, tender, dbc.GetConn());

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

        // Get All Tenders
        [HttpGet]
        [Route("GetAllTenders")]
        public IActionResult GetAllTenders()
        {
            DBconention dbc = new DBconention();
            var tenders = _dal.GetAllTenders(dbc.GetConn());

            if (tenders != null && tenders.Count > 0)
            {
                return Ok(tenders);
            }
            else
            {
                return NoContent(); // Returns 204 if no tenders are found
            }
        }

        [HttpGet]
        [Route("GetTender/{id}")]
        public IActionResult GetTender(int id)
        {
            DBconention dbc = new DBconention();
            Tender tender = _dal.GetTenderById(id, dbc.GetConn());

            if (tender == null)
            {
                return NotFound(new Response { StatusCode = 404, StatusMessage = $"Tender with ID {id} not found" });
            }

            return Ok(tender);
        }

        [HttpDelete]
        [Route("DeleteTender/{id}")]
        public IActionResult DeleteTender(int id)
        {
            if (id <= 0)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid tender ID" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.DeleteTender(id, dbc.GetConn());

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
