using Agora.BLL;
using Agora.Models;
using Microsoft.AspNetCore.Mvc;

namespace Agora.WebAPI.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    [Produces("application/json")]
    public class MasterController : Controller
    {
        private readonly IMasterManager _masterManager;
        public MasterController( IMasterManager masterManager) {
            _masterManager = masterManager;
        }
        [HttpGet]
        public async Task<IActionResult> GetMasters()
        {
            var result = await _masterManager.GetMasters();
            return new JsonResult(result);
        }
        [HttpGet("option/{idMaster:Guid}")]
        public async Task<IActionResult> GetOptions(Guid idMaster)
        {
            var result = await _masterManager.GetOptions(idMaster);
            return new JsonResult(result);
        }
        [HttpPut]
        [HttpPost]
        public async Task SetMaster(Master master)
        {
            await _masterManager.SetMaster(master);
        }
		[HttpPost("option/")]
		[HttpPut("option/")]
        public async Task SetOptions(MasterOption[] option)
        {
            await _masterManager.SetOptions(option);
        }
        [HttpDelete("{idMaster:Guid}")]
        public async Task RemoveMaster(Guid idMaster)
        {
            await _masterManager.RemoveMaster(idMaster);
        }
		
		[HttpDelete("option/{idOption:Guid}")]
        public async Task RemoveOptions(Guid idOption)
        {
            await _masterManager.RemoveOptions(idOption);
        }
    }
}
