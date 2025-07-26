using AAAA.Models;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using WebApplication5.Models;
using WebApplication5.Util;

namespace WebApplication5.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class DrugController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal; // Data access layer

        public DrugController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        // Add Drug
        [HttpPost]
        [Route("AddDrug")]
        public IActionResult AddDrug([FromBody] Drug drug)
        {
            if (drug == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid Data" });
            }
            DBconention dbc = new DBconention();
            Response response = _dal.AddDrug(drug, dbc.GetConn());

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

        // Update Drug
        [HttpPut]
        [Route("UpdateDrug/{id}")]
        public IActionResult UpdateDrug(int id, [FromBody] Drug drug)
        {
            if (drug == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid Data" });
            }

            DBconention dbc = new DBconention();
            Response response = _dal.UpdateDrug(id, drug, dbc.GetConn());

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

        // Display All Drugs
        [HttpGet]
        [Route("GetAllDrugs")]
        public IActionResult GetAllDrugs()
        {
            try
            {
                DBconention dbc = new DBconention();
                var drugList = _dal.GetAllDrugs(dbc.GetConn());
                if (drugList != null && drugList.Any())
                {
                    return Ok(drugList);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No drugs found" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        // Search Drug by Name
        [HttpGet]
        [Route("SearchDrug")]
        public IActionResult SearchDrug([FromQuery] string name)
        {
            if (string.IsNullOrEmpty(name))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Drug name is required" });
            }

            try
            {
                DBconention dbc = new DBconention();
                var drugList = _dal.SearchDrugByName(name, dbc.GetConn());

                if (drugList != null && drugList.Any())
                {
                    return Ok(drugList);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "Drug not found" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        // **Delete Drug**
        [HttpDelete]
        [Route("DeleteDrug/{id}")]
        public IActionResult DeleteDrug(int id)
        {
            try
            {
                DBconention dbc = new DBconention();
                Response response = _dal.DeleteDrug(id, dbc.GetConn());

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
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }
    }
}
