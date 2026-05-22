using Agora.BLL;
using Agora.Models;
using Microsoft.AspNetCore.Mvc;

namespace Agora.WebAPI.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class TemplateController : Controller
    {
        private readonly ITemplateManager _templateManager;
        public TemplateController(ITemplateManager templateManager)
        {
            _templateManager = templateManager;
        }
        #region Template
        [HttpGet]
        public async Task<IActionResult> GetTemplates()
        {
            var result = await _templateManager.GetTemplates();
            return new JsonResult(result);
        }
        [HttpPut]
        [HttpPost]
        public async Task SetTemplate(Template template)
        {
            await _templateManager.SetTemplate(template);
        }
        [HttpDelete("{idTemplate:Guid}")]
        public async Task RemoveTemplate(Guid idTemplate)
        {
            await _templateManager.RemoveTemplate(idTemplate);
        }
        #endregion

        #region Field
        [HttpGet("field/{idTemplate:Guid}")]
        public async Task<IActionResult> GetFields(Guid idTemplate)
        {
            var result = await _templateManager.GetFields(idTemplate);
            return new JsonResult(result);
        }
        [HttpPost("field/")]
        [HttpPut("field/")]
        public async Task SetFields(TemplateField[] fields)
        {
            await _templateManager.SetFields(fields);
        }
        [HttpDelete("field/{idField:Guid}")]
        public async Task RemoveField(Guid idField)
        {
            //Console.WriteLine(System.Text.Json.JsonSerializer.Serialize(fields));
            await _templateManager.RemoveField(idField);
        }
        #endregion

        #region Children
        [HttpGet("children/{idTemplate:Guid}")]
        public async Task<IActionResult> GetChildrens(Guid idTemplate)
        {
            var result = await _templateManager.GetChildrens(idTemplate);
            return new JsonResult(result);
        }
        [HttpGet("children/node/{idNode:Guid}")]
        public async Task<IActionResult> GetChildrenByIdNode(Guid idNode)
        {
            var result = await _templateManager.GetChildrenByIdNode(idNode);
            return new JsonResult(result);
        }
        [HttpPost("children/")]
        [HttpPut("children/")]
        public async Task SetChildrens(TemplateAllowedChildren[] childrens)
        {
            await _templateManager.SetChildrens(childrens);
        }
        [HttpDelete("children/{idParent:Guid}/{idChild:Guid}")]
        public async Task RemoveChildren(Guid idParent,Guid idChild)
        {
            await _templateManager.RemoveChildren(idParent,idChild);
        }
        #endregion

    }
}
