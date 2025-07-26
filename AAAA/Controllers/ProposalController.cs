using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using WebApplication5.Models;
using WebApplication5.Util;
using System;
using System.Linq;
using AAAA.Models;

namespace WebApplication5.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class ProposalController : ControllerBase
    {
        private readonly IConfiguration _configuration;
        private Dal _dal;

        public ProposalController(IConfiguration configuration)
        {
            _configuration = configuration;
            _dal = new Dal();
        }

        // Add a proposal
        [HttpPost]
        [Route("AddProposal")]
        public IActionResult AddProposal([FromBody] Proposal proposal)
        {
            if (proposal == null)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid Data" });
            }
            try
            {
                DBconention dbc = new DBconention();
                Response response = _dal.AddProposal(proposal, dbc.GetConn());
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

        // Get all proposals
        [HttpGet]
        [Route("GetAllProposals")]
        public IActionResult GetAllProposals()
        {
            try
            {
                DBconention dbc = new DBconention();
                var proposalList = _dal.GetAllProposals(dbc.GetConn());
                if (proposalList != null && proposalList.Any())
                {
                    return Ok(proposalList);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No proposals found" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }

        // Accept a proposal
        [HttpPost]
        [Route("AcceptProposal/{tenderId}")]
        public IActionResult AcceptProposal(int tenderId)
        {
            if (tenderId <= 0)
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid tender ID" });
            }

            try
            {
                DBconention dbc = new DBconention();
                Response response = _dal.AcceptProposal(tenderId, dbc.GetConn());

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
                return StatusCode(StatusCodes.Status500InternalServerError,
                    new Response
                    {
                        StatusCode = 500,
                        StatusMessage = $"Error accepting proposal: {ex.Message}"
                    });
            }
        }

        // Get proposals by supplier email
        [HttpGet]
        [Route("GetProposalsBySupplier")]
        public IActionResult GetProposalsBySupplier(string email)
        {
            if (string.IsNullOrEmpty(email))
            {
                return BadRequest(new Response { StatusCode = 400, StatusMessage = "Invalid email" });
            }
            try
            {
                DBconention dbc = new DBconention();
                var proposalList = _dal.GetProposalsBySupplier(email, dbc.GetConn());
                if (proposalList != null && proposalList.Any())
                {
                    return Ok(proposalList);
                }
                else
                {
                    return NotFound(new Response { StatusCode = 404, StatusMessage = "No proposals found for this supplier" });
                }
            }
            catch (Exception ex)
            {
                return StatusCode(StatusCodes.Status500InternalServerError, new Response { StatusCode = 500, StatusMessage = ex.Message });
            }
        }
    }
}