using Agora.BLL;
using Agora.Models;
using Microsoft.AspNetCore.Mvc;

namespace Agora.WebAPI.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class NodeController : Controller
    {
        private readonly INodeManager _nodeManager;
        public NodeController(INodeManager nodeManager)
        {
            _nodeManager = nodeManager;
        }
        #region Root
        [HttpGet("root/")]
        public async Task<IActionResult> GetNodeRoot()
        {
            var result = await _nodeManager.GetNodeRoot();
            return new JsonResult(result);
        }
        [HttpGet("tree/{IdNode:Guid}")]
        public async Task<IActionResult> GetNodesTree(Guid IdNode)
        {
            var result = await _nodeManager.GetNodesList(IdNode);
            return new JsonResult(result);
        }
        [HttpPost("root/")]
        [HttpPut("root/")]
        public async Task SetNodeRoot(Node node)
        {
            await _nodeManager.SetNodeRoot(node);
        }
        [HttpDelete("root/{IdNode:Guid}")]
        public async Task RemoveNodeRoot(Guid IdNode)
        {
            await _nodeManager.RemoveNodeRoot(IdNode);
        }
        #endregion

        #region Node
        [HttpPut]
        [HttpPost]
        public async Task SetNode(NodeList node)
        {
            await _nodeManager.SetNode(node);
        }
        [HttpDelete("{IdNode:Guid}")]
        public async Task RemoveNode(Guid IdNode)
        {
            await _nodeManager.RemoveNode(IdNode);
        }
        #endregion
    }
}